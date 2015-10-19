<?php
namespace Craft;

/**
* Element Action to queue emails
*/
class Emailer_SendEmailElementAction extends BaseElementAction
{
  public function getName()
  {
    return Craft::t('Send Emails');
  }
  
  public function getConfirmationMessage()
  {
    return Craft::t('Are you sure you wish to send out emails?');
  }
  
  public function performAction(ElementCriteriaModel $criteria)
  {
    $elements = $criteria->find();
    
    foreach ($elements as $element) {
      
      EmailerPlugin::log('Queuing entry: '.$element->title);
      
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
      
      $task = craft()->tasks->createTask('Emailer', 'Email: '.$element->title, array(
        'email' => $email,
        'recipients' => explode(',', $settings->getAttribute('testers')),
      ));
      
      EmailerPlugin::log('Queued '.$email->subject.' to be sent to '.$settings->getAttribute('testers'));
    }
    
    $this->setMessage(Craft::t('Emails queued successfully'));
    return true;
  }
}


