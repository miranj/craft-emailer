<?php
namespace Craft;

/**
 * Emailer task
 */
class EmailerTask extends BaseTask
{
  protected $email = false;
  
  /**
   * Defines the settings.
   *
   * @access protected
   * @return array
   */
  protected function defineSettings()
  {
    return array(
      'email_id' => AttributeType::String,
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
    return 'Send email ID '.$this->getSettings()->email_id;
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
  
  
  public function getEmail()
  {
    $element_id = $this->getSettings()->email_id;
    
    // Look for it in the cache
    if ($this->email) {
      return $this->email;
    }
    
    // Cache miss, try to regenerate
    $element = craft()->elements->getElementById($element_id, ElementType::Entry);
    if ($element === null) {
      return false;
    }
    
    // Fetch template paths
    $templates = array();
    $section = $element->getSection();
    $templates['htmlBody'] = $section->template;
    $settings = craft()->plugins->getPlugin('Emailer')->getSettings();
    $templates['body'] = $settings->getAttribute('textTemplate');
    
    // Build the email object
    EmailerPlugin::log('Trying to build the email.');
    $email = new EmailModel();
    $email->subject = $element->title;
    
    // Ensure we are loading front-end templates
    // regardless of the origin of the request
    $oldPath = craft()->path->getTemplatesPath();
    $newPath = craft()->path->getSiteTemplatesPath();
    craft()->path->setTemplatesPath($newPath);
    
    // Render templates
    EmailerPlugin::log('Trying to render templates');
    foreach ($templates as $attribute => $template) {
      if (craft()->templates->doesTemplateExist($template)) {
        $email->{$attribute} = craft()->templates->render($template, array( 'entry' => $element ));
      }
    }
    
    // Restore the template path to its previous state
    craft()->path->setTemplatesPath($oldPath);
    
    // Update cache
    $this->email = $email;
    
    return $email;
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
      $recipient = $this->getSettings()->recipients[$step];
      EmailerPlugin::log('Trying to send email to '.$recipient);
      
      $this->getEmail();
      if (!$this->email) {
        throw new \Exception(Craft::t('Unable to fetch rendered email.'));
      }
      $this->email->toEmail = $recipient;
      craft()->email->sendEmail( $this->email );
      
    } catch ( \Exception $e ) {
      EmailerPlugin::log('Failed to send email.', LogLevel::Error);
      EmailerPlugin::log($e, LogLevel::Error);
      
      // Do nothing
      return false;
    }
    
    sleep(0.1);
    return true;
  }
}
