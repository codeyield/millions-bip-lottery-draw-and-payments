<?php
/*********************************************************************
 * This script randomly draws several thousand prizes from the main bank 
 * of the Millions BIP Lottery, and then makes massive payments to the winners' wallets.
 * Please, see readme.md for details.
 */

define('INDEXDIR',  dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('VENDORDIR', realpath(INDEXDIR . '../vendor')  . DIRECTORY_SEPARATOR);
define('SECRETDIR', realpath(INDEXDIR . '../secrets') . DIRECTORY_SEPARATOR);
define('TEMPDIR',   realpath(INDEXDIR . 'temp') . DIRECTORY_SEPARATOR);

require_once(VENDORDIR . 'autoload.php');
require_once(SECRETDIR . 'secrets.inc.php');
require_once(INDEXDIR  . 'config.inc.php');
require_once(INDEXDIR  . 'playout.php');
require_once(INDEXDIR  . 'logger.php');


use Minter\MinterAPI;
use Minter\SDK\MinterTx;
use Minter\SDK\MinterWallet;
use Minter\SDK\MinterCoins\MinterSendCoinTx;
use Minter\SDK\MinterCoins\MinterMultiSendTx;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;


	set_time_limit(0);

	/*********************************************************************
	 * Prize drawing structure, according to the Millions BIP Lottery rules
	 */
	const RANDOM_PRIZES = [
		['level' => 4, 'qty' =>  100, 'amount' => '73.39'],
		['level' => 5, 'qty' =>  250, 'amount' => '14.68'],
		['level' => 6, 'qty' =>  500, 'amount' =>  '7.34'],
		['level' => 7, 'qty' => 1000, 'amount' =>  '3.67'],
		['level' => 8, 'qty' => 2500, 'amount' =>  '1.17'],
	];

	// Notice messages array
	$msginfo = [];


	/*********************************************************************
	 * Load the wallets of all participants in the Millions BIP Lottery
	 * The list of wallets has been pulled from the official lottery spreadsheet and put into this repository
	 */
	$wallets = file('all_wallets.txt', FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);

	$wallets = array_filter($wallets, function($val) {
		return preg_match('/Mx[0-9A-f]{40}/i', $val);
	});


	/*********************************************************************
	 * Initialize the Telegram Bot API
	 */
	try {
		$bot = new \TelegramBot\Api\BotApi(TELEGRAM_BOT_TOKEN);
		
	} catch (\TelegramBot\Api\Exception $e) {
		Logger::error($msginfo[] = 'Can`t initialize the Telegram Bot API', [$e->getMessage()]);
		exit;
	}


	/*********************************************************************
	 * Come on a random draw
	 */
	$draw = new Playout();
	
	foreach(RANDOM_PRIZES as $item) {
		
		$numberOfPrizes = $item['qty'];
		
		// Magic random =)
		$winners = $draw->random($wallets, $numberOfPrizes);
		
		
		Logger::info($msginfo[] = count($winners) . ' prizes have been drawn');
		Logger::info($msginfo[] = count($wallets) . ' wallets left on the list for the next draw(s)');
		
		
		$winnersFile = TEMPDIR . '/' . WINNERS_FILE_PREFIX . $numberOfPrizes . '.txt';
		
		// Fix the list of winning wallets
		file_put_contents($winnersFile, implode("\n", $winners));
		
		
		// Posting to the Telegram channel below
		$document = new \CURLFile(realpath($winnersFile));
		
		$replacements = [
			'%%PRIZES%%' => $numberOfPrizes,
			'%%GITHUB%%' => GITHUBSOURCE_URL,
		];
    	
		try {
			$result = $bot->sendDocument(LOTTERY_CHANNEL1, $document, strtr(TEXT_WINPOST_RU, $replacements), null, null, null, 'Markdown');
			
			if(($postid = $result->getMessageId()) > 0) {
				Logger::info($msginfo[] = 'Post was published in '.LOTTERY_CHANNEL1.' with id #'.$postid);
			}
			
		} catch (\TelegramBot\Api\Exception $e) {
			Logger::error($msginfo[] = 'Can`t publish post in '.LOTTERY_CHANNEL1, [$e->getMessage()]);
		}
		
		try {
			$result = $bot->sendDocument(LOTTERY_CHANNEL2, $document, strtr(TEXT_WINPOST_EN, $replacements), null, null, null, 'Markdown');
			
			if(($postid = $result->getMessageId()) > 0) {
				Logger::info($msginfo[] = 'Post was published in '.LOTTERY_CHANNEL2.' with id #'.$postid);
			}
			
		} catch (\TelegramBot\Api\Exception $e) {
			Logger::error($msginfo[] = 'Can`t publish post in '.LOTTERY_CHANNEL2, [$e->getMessage()]);
		}
		
	}


	// Function to compiling a wallets' list for tx object
	$makeListOfWallets = function($to, $value) {
		return ['to' => $to, 'value' => $value, 'coin' => LOTTERY_COIN];
	};

	$api = new MinterAPI(new \GuzzleHttp\Client(NODEAPI));


	/*********************************************************************
	 * Payout of prizes
	 */
	foreach(RANDOM_PRIZES as $item) {
		
		$numberOfPrizes = $item['qty'];
		
		$winnersFile = TEMPDIR . '/' . WINNERS_FILE_PREFIX . $numberOfPrizes . '.txt';
		
		$wallets = @file($winnersFile, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
		
		if(empty($wallets)) {
			Logger::error($msginfo[] = 'Can`t open or empty file '.$winnersFile);
			continue;
		}

		// No more than 100 wallets in one multisend transaction
		foreach(array_chunk($wallets, 100) as $partOfWallets) {
		
			/*********************************************************************
			 * Multisend payment for winners
			 */
			try {
				$tx = new MinterTx([
					'nonce'    => $api->getNonce(LOTTERY_WALLET),
					'chainId'  => MinterTx::MAINNET_CHAIN_ID,
					'gasPrice' => 1,
					'gasCoin'  => LOTTERY_COIN,
					'type'     => MinterMultiSendTx::TYPE,
					'data'     => [
						'list' => array_map($makeListOfWallets, $partOfWallets, array_fill(0, count($partOfWallets), $item['amount']))
					],
					'payload'       => TEXT_PAYLOAD,
					'serviceData'   => '',
					'signatureType' => MinterTx::SIGNATURE_MULTI_TYPE,
				]);
				
				$signedTx = $tx->signMultisigBySigns(LOTTERY_WALLET, [
					$tx->createSignature(MinterWallet::mnemonicToPrivateKey(MNEMONIC1)), 
					$tx->createSignature(MinterWallet::mnemonicToPrivateKey(MNEMONIC2))
				]);

				$resp = $api->send($signedTx);
				
				// Take the transaction hash if success
				if(isset($resp->result->code, $resp->result->hash) and ($resp->result->code == 0)) {

					$txPay = 'Mt'.strtolower($resp->result->hash);
					Logger::info($msginfo[] = "Pay for {$numberOfPrizes} wallets: {$txPay}");
				}
				
				// Wait for next block height. Max one send transaction from wallet per block height!
				sleep(6);

			} catch(RequestException | ConnectException $e) {
				Logger::error($msginfo[] = 'Error sending transaction ', [$e->getMessage()]);
				continue;
			}
		
		}
	}


	Logger::info($msginfo[] = 'Millions BIP Lottery draw completed!');
	
	echo '<pre>' . implode("\n", $msginfo) . "\n";
	exit(0);
