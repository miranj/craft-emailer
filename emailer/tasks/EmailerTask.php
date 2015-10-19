<?php
namespace Craft;

/**
 * Emailer task
 */
class EmailerTask extends BaseTask
{
  /**
   * Defines the settings.
   *
   * @access protected
   * @return array
   */
  protected function defineSettings()
  {
    return array(
      'email' => AttributeType::Mixed,
      'recipients' => array(AttributeType::Mixed, 'default' => array()),
    );
  }
  
  /**
   * Returns the default description for this task.
   *
   * @return string
   */
  public function getDescription()
  {
    return 'Send email '.$this->getSettings()->email->subject;
  }
  
  /**
   * Gets the total number of steps for this task.
   *
   * @return int
   */
  public function getTotalSteps()
  {
    return count($this->getSettings()->recipients);
  }
  
  /**
   * Runs a task step.
   *
   * @param int $step
   * @return bool
   */
  public function runStep($step)
  {
    try {
      EmailerPlugin::log('Trying to send the email.');
      
      $email = $this->getSettings()->email;
      $email->toEmail = $this->getSettings()->recipients[$step];
      craft()->email->sendEmail( $email );
      
    } catch ( \Exception $e ) {
      EmailerPlugin::log('Failed to send email.');
      EmailerPlugin::log($e);
      
      // Do nothing
      return false;
    }
    
    sleep(0.1);
    return true;
  }
}
