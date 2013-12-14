<?
class timer {
   private $timer;

   public function __construct( $autostart = false ) {
      $this->reset();
      if( $autostart ) $this->start();
   }

   public function start() {
      if( $this->isActive() ) throw new timerException( "Timer already running" );
      $this->timer->startTime = microtime( true );

      return true;
   }

   public function stop() {
      $this->checkActive();

      $this->timer->endTime = microtime( true );
      return $this->calculateInterval( $this->timer->startTime, $this->timer->endTime );
   }

   public function split() {
      $this->checkActive();
      if( $this->isPaused() ) throw new timerException( "Cannot split a paused timer" );

      $splitInfo = new stdClass;
      $splitInfo->splitTime = microtime( true );

      if( $this->hasSplits() ) {
         $splitIndex = sizeof( $this->timer->splits ) - 1;
         $lastSplit = $this->timer->splits[ $splitIndex ]->splitTime;
      } else {
         $lastSplit = $this->timer->startTime;
      }
      $splitDelta = $splitInfo->splitTime - $lastSplit;

      $this->timer->splits[] = clone $splitInfo;
      return $splitDelta;
   }

   public function unsplit() {
      return false;
   }

   public function suspend() { return $this->pause(); }
   public function pause() {
      $this->checkActive();
      if( $this->isPaused() ) return;

      $pauseInfo = new stdClass;
      $pauseInfo->pauseTime = microtime( true );
      $this->timer->pauses[] = clone $pauseInfo;

      return true;

   }

   public function resume() { return $this->unpause(); }
   public function unpause() {
      $this->checkActive();
      if( ! $this->isPaused() ) throw new timerException( "Timer is not paused" );

      $unpauseTime = microtime( true );
      $pauseIndex = sizeof( $this->timer->pauses ) - 1;

      $pauseTime = $this->timer->pauses[ $pauseIndex ]->pauseTime;
      $pauseInterval = $unpauseTime - $pauseTime;
      $this->timer->pauses[ $pauseIndex ]->unpauseTime = $unpauseTime;
      $this->timer->pauses[ $pauseIndex ]->pauseLength = $pauseInterval;

      if( $this->hasSplits() ) {
         $splitIndex = sizeof( $this->timer->splits ) - 1;
         $this->timer->splits[ $splitIndex ]->pauseLength += $pausedInterval;
      }

      $this->timer->pauseLength += $pauseInterval;

      return $pauseInterval;
   }

   public function getSplitTimes() {

   }

   public function debugMe() {
      return $this->timer;
   }

   public function reset() {
      unset( $this->timer );
      $this->timer = new stdClass;

      $this->timer->startTime = null;
      $this->timer->pauseLength = null;
      $this->timer->endTime = null;
      $this->timer->splits = array();
      $this->timer->pauses = array();
   }




   private function isActive() {
      if( is_null( $this->timer->startTime )) $retval = false; else $retval = true;
      return $retval;
   }

   private function isPaused() {
      $retval = false;
      if( ! sizeof( $this->timer->pauses ) == 0 ) {
         $pauseIndex = sizeof ( $this->timer->pauses ) - 1;
         if( ! isset( $this->timer->pauses[ $pauseIndex ]->unpauseTime )) $retval = true;
      }

      return $retval;
   }

   private function hasSplits() {
      if( sizeof( $this->timer->splits ) > 0 ) $retval = true; else $retval = false;
      return $retval;
   }

   private function calculateInterval( $time1, $time2 ) {
   }

   private function checkActive() {
      if( ! $this->isActive() ) throw new timerException( "Timer not started" );
      return true;
   }

}

class timerException extends Exception { }

?>
