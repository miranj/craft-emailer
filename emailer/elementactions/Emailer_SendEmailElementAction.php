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
    $settings = craft()->plugins->getPlugin('Emailer')->getSettings();
    $recipients = explode(',', $settings->getAttribute('testers'));
    
    foreach ($elements as $element) {
      EmailerPlugin::log('Queuing entry: '.$element->title);
      
      $task = craft()->tasks->createTask('Emailer', 'Email: '.$element->title, array(
        'email_id' => $element->id,
        'recipients' => $recipients,
      ));
      
      EmailerPlugin::log('Queued '.$element->title.' to be sent to '.count($recipients).' recipients.');
    }
    
    $this->setMessage(Craft::t('Emails queued successfully'));
    return true;
  }
}


