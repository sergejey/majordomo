<?php

//Добавить в файл своих функций Получить детали 
// события по его имени
function getEventDetails($eventName) { $event = 
 SQLSelectOne("SELECT * FROM events WHERE 
 EVENT_NAME='".$eventName."'"); if (is_array ($event 
 )) {
  return $event['DETAILS'];
 } else {
  return false;
 }
}

//Добавить в файл своих функций Получить "срок 
//годности" события
function getEventExpire($eventName){ $event = 
 SQLSelectOne("SELECT UNIX_TIMESTAMP(EXPIRE) t FROM 
 events WHERE EVENT_NAME='".$eventName."'"); if 
 (is_array ($event )) {
  return $event['t'];
 } else {
  return false;
 }
}


//Добавить в файл своих функций Удалить событие по 
// его имени
function EventDelete($eventName) { $fn = 
 SQLExec("DELETE FROM events WHERE 
 EVENT_NAME='".$eventName."'"); return $fn;
}

// Возвращает оставшееся время в секундах работы таймера по его имени.
// Если таймера нет, вернет 0
function timeOutResidue($title) {
  $timerId=timeOutExists($title);
  if ($timerId) {
   $timer_job=SQLSelectOne("SELECT UNIX_TIMESTAMP(RUNTIME) as TM FROM jobs WHERE ID='".$timerId."'");
   $diff=(int)$timer_job['TM']-time(); // получаем время в секундах, оставшееся до запланированного срабатывания таймера
    return $diff;
   } else {
    return 0;
   }
 }
 
function timeUpdated($updated) { 
 $passed = time() - $updated;
     if ($passed > 28800) 
     {
   $updatedText = date('d/m/y H:i', $updated);
     }
     if ($passed < 28800) 
     {
   $updatedText = date('H:i', $updated);
     }
     return $updatedText;
}


function timeUpdatedText($updatedTime) {
   //$updatedTime = getObject($obj)->getProperty($sv);
     $passed = time() - $updatedTime;
     if ($passed > 28800) 
     {
   $string1 = date('d/m/y H:i', $updatedTime);
     }

      if ($passed < 10) 
  {
  $string1 = 'только что';
  }
  elseif ($passed < 60) 
  {
  //$string1 = myMorph($passed,'секунда','секунды','секунд').' назад';
  $string1 = $passed .' '. GetNumberWord($passed,array('секунда','секунды','секунд')).' назад';
  }
  elseif ($passed < 3600) 
  {
  $passed_m = round($passed / 60);
  //$string1 = myMorph($passed_m,'минута','минуты','минут').' назад';
  $string1 = $passed_m .' '. GetNumberWord($passed_m,array('минута','минуты','минут')).' назад';
  }
  elseif ($passed < 28800) 
  {
  $h0 = (int)date('G', time());
  $h1 = (int)date('G', $updatedTime);
    if ($h0 > $h1) 
    {
    $string1 = 'сегодня в ' . date('H:i', $updatedTime); //небыло перехода суток
    } 
    else 
    {
    $string1 = 'вчера в ' . date('H:i', $updatedTime); //был переход суток
    }
    } 
  else 
  {
  $string1 = date('d/m/y H:i', $updatedTime); //прошло более 8 часов, укажем время с датой
  }
  return $string1;
}
