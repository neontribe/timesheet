<?php

/**  
 * @file  
 * Contains Drupal\timesheet\Form\Admin\TimesheetForm.  
 */  
namespace Drupal\timesheet\Form\Admin;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class TimesheetForm extends ConfigFormBase {
  /**  
   * {@inheritdoc}  
   */  
  protected function getEditableConfigNames() {  
    return [  
      'timesheet.adminsettings',  
    ];  
  }  

  /**  
   * {@inheritdoc}  
   */  
  public function getFormId() {  
    return 'timesheet_form';  
  }  

  /**  
   * {@inheritdoc}  
   */  
  public function buildForm(array $form, FormStateInterface $form_state) {  
    $config = $this->config('timesheet.adminsettings');  

    $form['timesheet_include_main_nav'] = [  
      '#type' => 'checkbox',  
      '#title' => $this->t('Main nav'),  
      '#description' => $this->t('Include link in main navigation menu.'),  
      '#default_value' => $config->get('timesheet_include_main_nav'),  
    ];  

    return parent::buildForm($form, $form_state);  
  }  

    /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('timesheet.adminsettings')
      ->set('timesheet_include_main_nav', $form_state->getValue('timesheet_include_main_nav'))
      ->save();
  }

}

// vim: set filetype=php expandtab tabstop=2 shiftwidth=2 autoindent smartindent:

