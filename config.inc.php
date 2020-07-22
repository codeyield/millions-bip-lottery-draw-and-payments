<?php

/*********************************************************************
 * Official Minter wallet and telegram channel of the Millions BIP Lottery 
 */
const LOTTERY_WALLET   = 'Mxd1267678bc5a37e7ab5ff6375ba5ebd391de2f36';
const LOTTERY_CHANNEL1 = '@millionsbiplottery';
const LOTTERY_CHANNEL2 = '@millionsbiplottery_en';
const LOTTERY_COIN     = 'BIP';


/*********************************************************************
 * Other data
 */
const GITHUBSOURCE_URL = 'https://github.com/codeyield/millions-bip-lottery-draw-and-payments';
const EXPLORER_TXURL   = 'https://minterscan.net/tx/';

const WINNERS_FILE_PREFIX = 'Won_Wallets_Prizes_Of_';


/*********************************************************************
 * Minter Nodes API
 * @url https://docs.minter.network/#tag/Node-API
 */
const NODEAPI = [
	'base_uri'        => 'https://mnt.funfasy.dev/',
	'verify'          => false,
	'connect_timeout' => 10,
	'timeout'         => 30,
	'headers'         => [
		'Content-Type'     => 'application/json',
		'X-Project-Id'     => FUNFASY_ID,
		'X-Project-Secret' => FUNFASY_SECRET,
	]
];


/*********************************************************************
 * Content for the posting
 */
const TEXT_PAYLOAD = 'Congrats to the winners of the @millionsbiplottery';

const TEXT_WINPOST_RU = <<<EOF
🎰 Список выигравших кошельков автоматического случайного розыгрыша *Millions BIP Lottery*. Было разыграно *%%PRIZES%%* призов.

_Этот розыгрыш проведён при помощи автоматического скрипта с открытым кодом на_ [Github](%%GITHUB%%). 
EOF;

const TEXT_WINPOST_EN = <<<EOF
🎰 List of *Millions BIP Lottery* automatic random draw wallets that have won. *%%PRIZES%%* prizes were drawn.

_This draw was held using an open source automated script published on_ [Github](%%GITHUB%%).
EOF;
