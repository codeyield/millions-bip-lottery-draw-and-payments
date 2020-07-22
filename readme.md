## Millions BIP Lottery - Main bank drawing

### Overview

**[EN]**

This script randomly draws several thousand prizes from the main bank of the Millions BIP Lottery, and then makes massive payments to the winners' wallets.

For the draw, the complete list of the lottery ticket buyers' wallets is used in the **all_wallets.txt** file, which was downloaded from the official lottery spreadsheet to this repository. This file also tagged 23 lines with the top winners' wallets, as the biggest prizes were previously raffled live. Accordingly, these dropped out wallets no longer participate in the drawing in accordance with the Lottery Rules.

The mechanics of the draw is to randomly select one line at a time from the list of ticket buyers. The wallet in the drop-down line is considered to be the winner, and this line is removed from the list of ticket buyers before the next choice (or marked as deleted and excluded from subsequent selections).

For random draw, the [Mersenne Twister](http://www.math.sci.hiroshima-u.ac.jp/~m-mat/MT/emt.html) math algorithm is used.

Official Telegram channel: [@millionsbiplottery_en](https://t.me/millionsbiplottery_en)

**[RU]**

Этот скрипт случайно разыгрывает несколько тысяч призов главного банка лотереи Millions BIP Lottery, и затем осуществляет массовые выплаты на кошельки победителей.

Для розыгрыша используется полный список кошельков покупателей билетов лотереи в файле **all_wallets.txt**, который был выгружен из официальной электронной таблицы лотереи в этот репозиторий. В этом файле также помечены 23 строки с кошельками топ победителей, поскольку самые крупные призы были ранее разыграны в прямом эфире. Соответственно, выпавшие кошельки уже не участвуют в розыгрыше согласно Правил лотереи.

Механика розыгрыша состоит в случайном выборе одной строки за раз из списка покупателей билетов. Кошелек в выпавшей строке считается выигравшим, а данная строка удаляется из списка покупателей билетов перед следующим выбором (или помечается как удаленная и исключается из последующих выборов).

Для случайного выбора используется математический алгоритм [Вихря Мерсена](http://www.math.sci.hiroshima-u.ac.jp/~m-mat/MT/emt.html).

Официальный Телеграм канал: [@millionsbiplottery](https://t.me/millionsbiplottery)

### Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

### Credits

* [Minter Node API](https://docs.minter.network/#tag/Node-API)
* [Minter PHP SDK](https://github.com/MinterTeam/minter-php-sdk)
* [Simple PHPLogger](https://github.com/advename/Simple-PHP-Logger)
