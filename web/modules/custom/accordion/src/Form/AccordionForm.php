<?php

/**
 * @file
 * Contains Drupal\accordion\Form\CustomModuleForm.
 */

namespace Drupal\accordion\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class AccordionForm extends ConfigFormBase {
    /**
    * {@inheritdoc}
    */
    public function getFormId() {
        return 'accordion_form';
    }

    /**
    * {@inheritdoc}
    */
    protected function getEditableConfigNames() {
        return [
            'accordion.settings',
        ];
    }

    /**
    * {@inheritdoc}
    */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $form = parent::buildForm($form, $form_state);
        $config = $this->config('accordion.settings');

        // $form['machine_name'] = [
        //     '#type' => 'machine_name',
        //     '#title' => $this->t('Accordion paragraph machine name'),
        //     '#default_value' => $config->get('accordion.machine_name'),
        // ];

        $form['style'] = [
            '#type' => 'select',
            '#title' => $this->t('Accordion Style'),
            '#default_value' => $config->get('accordion.style') ?: 'light',
            '#options' => [
              'light' => $this->t('Light'),
              'dark' => $this->t('Dark'),
              'vintage' => $this->t('Vintage'),
            ],
          ];

        $form['animation_length'] = [
            '#type' => 'range',
            '#title' => $this->t('Accordion animation length (ms)'),
            '#min' => 0,
            '#max' => 2500,
            '#default_value' => $config->get('accordion.animation_length'),
            '#description' => $this->t('Default value: 250ms</br>
                                        Minimum value: 0ms</br>
                                        Maximum value: 2500ms</br>
                                        Current value:' . strval($config->get('accordion.animation_length')) . 'ms'),
        ];

        return $form;
    }

    /**
    * {@inheritdoc}
    */
    public function validateForm(array &$form, FormStateInterface $form_state) {
        // Phone number verification
        // if (strlen($form_state->getValue('phone_number')) < 3) {
        //     $form_state->setErrorByName('phone_number', $this->t('The phone number is too short. Please enter a full phone number.'));
        // }
    }

    /**
    * {@inheritdoc}
    */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $config = $this->config('accordion.settings');
        // $config->set('accordion.machine_name', $form_state->getValue('machine_name'));
        $config->set('accordion.style', $form_state->getValue('style'));
        $config->set('accordion.animation_length', $form_state->getValue('animation_length'));
        $config->save();
        return parent::submitForm($form, $form_state);
    }
}