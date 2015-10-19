<?php
namespace Craft;

/**
 * Emailer plugin
 */
class EmailerPlugin extends BasePlugin
{
  public function getName()
  {
    return Craft::t('Emailer');
  }
  
  public function getVersion()
  {
    return '0.1';
  }
  
  public function getDeveloper()
  {
    return 'Miranj';
  }
  
  public function getDeveloperUrl()
  {
    return 'http://miranj.in';
  }
  
  public function defineSettings()
  {
    return array(
      'section' => array(AttributeType::Mixed),
      'textTemplate' => array(AttributeType::Template),
      'testers' => array(AttributeType::String),
    );
  }
  
  public function getSettingsHtml()
  {
    return craft()->templates->render('emailer/_settings', array(
      'settings' => $this->getSettings(),
    ));
  }
  
  public function addEntryActions($source)
  {
    if ($source == $this->getSettings()->section) {
      return array(
        'Emailer_SendEmail',
      );
    }
    
    return array();
  }
}
