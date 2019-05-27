<?php
/**
* SkypeBot
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 1.1
*/

chdir(dirname(__FILE__) . '/../');

include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");

set_time_limit(0);

include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");

$ctl = new control_modules();

if (!Defined('SETTINGS_SKYPE_CYCLE') || SETTINGS_SKYPE_CYCLE==0)  exit;

include_once(DIR_MODULES . 'patterns/patterns.class.php');

$pt = new patterns();

Define('DEVIDER', 'и');

$last_day    = date('d-M-y');
$last_minute = date('H:i');

$old_message = $latest_message = '';

$tmp = SQLSelectOne("SELECT * FROM shouts WHERE MEMBER_ID = 0 ORDER BY ADDED DESC");

$latest_message = $tmp['MESSAGE'];
$old_message    = $latest_message;

//проверяем, запущен ли Скайп. Если нет - пока вываливаемся

//Инициализируем Dbus
$dbus = new Dbus(Dbus::BUS_SESSION, true);

//Попытка вызова методов. Если ошибка - значит нам не удалось к скайпу подключиться
for($i = 0; $i < 5; $i++)
{
   try
   {
      //Подключаемся к скайпу
      $n = $dbus->createProxy('com.Skype.API', '/com/Skype', 'com.Skype.API');
           //Имя нашей программы, авторизация в скайпе
      $n -> Invoke('NAME MajorDoMo');
      //Используем последний протокол
      $n -> Invoke('PROTOCOL 8');
      break;
        }
   catch (Exception $e)
   {
      DebMes('Skype error : ' . $e->getCode() . '. Error message: ' . $e->getMessage());
      $n = null;
   }

   sleep(5);
}

if(i >= 5)
{
   $n = $dbus = null;
   DebMes('Skype error : 5 попыток');
   exit;
}

//пишем класс по обработке нотификаций
class phpSkype
{
   /*
    * Эту функцию мы будем использовать для проверки последних сообщений в скайпе.
    * Если вы не хотите, чтобы программа реагировала на ваши сообщения, используйте
    * preg_match('/RECEIVED/', $notify)
    */

   public static function notify ($notify)
   {
      //echo "$notify"."\n";
      #if (preg_match('#RECEIVED|SENT#Uis', $notify)) {
      if (preg_match('/RECEIVED/', $notify))
      {
         $message_id = explode(' ', $notify);
         //Вызываем обработчик сообщений
         bot::get_details($message_id[1]);
      }
   }
}

//пишем класс - бот по работе со скайпом
class bot
{
   private static $last_id;

   public static function get_details ($message_id)
   {
      global $n;
      global $pt;

      //Получаем id чата, используется для ответа
      $ch   = $n -> Invoke('GET CHATMESSAGE '.$message_id.' CHATNAME');
      //Получаем текст сообщения
      $mess = $n -> Invoke('GET CHATMESSAGE '.$message_id.' BODY');
      //Получаем автора сообщения
      $aut  = $n -> Invoke('GET CHATMESSAGE '.$message_id.' FROM_DISPNAME');

      /*
       * Теперь мы получим из строк, которые мы только что получили, нужные нам данные.
       * А именно: Автора сообщения, id чата и текст сообщения.
       */

      $author  = explode('FROM_DISPNAME ', $aut);
      $chat    = explode('CHATNAME ', $ch);
      $message = explode('BODY ', $mess);

      //Выводим в консоль автора и сообщение
      #echo $author[1].': '.$message[1]."\n";

      //на ping отвечаем pong - для тестирования приема сообщений скриптом. Если ответа нет - скорее всего скрипт остановлися.
      if (substr(strtolower($message[1]), 0, 4) == 'ping' )
      {
         $n->Invoke('CHATMESSAGE ' . $chat[1] . ' pong');
         return true;
      }

      //обработаем сообщения, начинающиеся с восклицательного знака !
      //оставил возможность для примера - можно будет удалить
      if ($message[1][0] == '!')
      {
         self::reply($chat[1], $message[1], $message_id);
         return true;
      }

      //на остальные сообщения - выполняем обычную обработку через модуль MajorDoMo
      $user = SQLSelectOne("SELECT ID FROM users WHERE SKYPE LIKE '" . $author[1] . "'");

      if (!$user['ID']) $user = SQLSelectOne("SELECT ID FROM users ORDER BY ID");

      $user_id = $user['ID'];

      $qrys  = explode(' ' . DEVIDER . ' ', $message[1]);
      $total = count($qrys);

      for($i = 0; $i < $total; $i++)
      {
         $room_id = 0;

         $rec = array();
         $rec['ROOM_ID']   = (int)$room_id;
         $rec['MEMBER_ID'] = $user_id;
         $rec['MESSAGE']   = htmlspecialchars($qrys[$i]);
         $rec['ADDED']     = date('Y-m-d H:i:s');
         SQLInsert('shouts', $rec);
         $pt->checkAllPatterns();
         getObject("ThisComputer")->raiseEvent("commandReceived", array("command"=>$qrys[$i]));
      }
   }

   public function reply ($chat, $message, $id)
   {
      global $n;

      /*
        Ответы на специальные команды, начинающиеся с восклицательного знака !
        Пока оставляю для примера. В дальнейшем можно удалить
      */

      self::$last_id = $message;

      if (self::$last_id <= $message)
      {
         $command = explode(' ', $message);

         switch ($command[0])
         {
            case '!test':
               $reply = 'It\'s work!';
               break;

            case '!help':
               $comm = explode('!help ', $message);
               $reply = '<здесь можно вывести какую-нибудь справку>';
               break;

            default:
               $reply = 'Используйте !help';
               break;
         }

         //Посылаем сообщение
         if ($reply != '') $n -> Invoke('CHATMESSAGE '.$chat.' '.$reply);
      }
      else
      {
         echo 'Уже отвечал!' . "\n";
      }
   }

   public static function send_Message($to_name,$s_message)
   {
      global $n;

      // Функция по имени пользователя должна отправить ему сообщение в чат

      //создадим чат по имени пользователя
      $tmp   = $n->Invoke('CHAT CREATE ' . $to_name);
      $tchat = explode(' ', $tmp);

      //если чат не создался - просто выходим
      if (count($tchat) > 1)
         $chat= $tchat[1];
      else
         return false;

      //отправляем сообщение, затем контролируем статус отправки
      $tmp = $n->Invoke('CHATMESSAGE ' . $chat . ' ' . $s_message);
      $res = explode('STATUS ', $tmp);

      if (count($res) > 1)
      {
         return ($res[1] == 'SENDING');
      }
      else
      {
         return false;
      }
   }
}

//регистрируем класс нотификаций

//Регистрируем просмотр уведомлений скайпа
$dbus->registerObject('/com/Skype/Client', 'com.Skype.API.Client', 'phpSkype');

//запускаем цикл обработки событий
while(1)
{
   $s = $dbus->waitLoop(1);

   //echo "Running skypebot...\n";

   $tmp = SQLSelectOne("SELECT * FROM shouts WHERE MEMBER_ID = 0 ORDER BY ADDED DESC");
   $latest_message = $tmp['MESSAGE'];

   if ($old_message != $latest_message)
   {
      $old_message = $latest_message;

      if (isset($tmp['IMPORTANCE']) && $tmp['IMPORTANCE'] > 0)
      {
         $users = SQLSelect("SELECT * FROM users WHERE SKYPE != ''");
         $total = count($users);

         for($i = 0; $i < $total; $i++)
         {
            echo "Sending to " . $users[$i]['SKYPE'] . ": " . convert_cyr_string(iconv('UTF-8', 'WINDOWS-1251', $latest_message), 'w', 'd') ."\n";
            bot::send_Message(trim($users[$i]['SKYPE']), $latest_message);
         }
      }
   }

   if (file_exists('./reboot') || IsSet($_GET['onetime']))
   {
      $n    = null;
      $dbus = null;
      exit;
   }
}
