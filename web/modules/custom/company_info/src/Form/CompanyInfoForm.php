<?php

/**
 * @file
 * Contains Drupal\company_info\Form\CustomModuleForm.
 */

namespace Drupal\company_info\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class CompanyInfoForm extends ConfigFormBase {
    /**
    * {@inheritdoc}
    */
    public function getFormId() {
        return 'company_info_form';
    }

    /**
    * {@inheritdoc}
    */
    protected function getEditableConfigNames() {
        return [
            'company_info.settings',
        ];
    }

    /**
    * {@inheritdoc}
    */
    public function buildForm(array $form, FormStateInterface $form_state) {
        // Form constructor.
        $form = parent::buildForm($form, $form_state);
        // Default settings.
        $config = $this->config('company_info.settings');

        $form['name'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Company name'),
            '#default_value' => $config->get('company_info.name'),
        ];

        $form['address'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Company Address'),
            '#default_value' => $config->get('company_info.address'),
        ];

        $form['phone_number'] = [
            '#type' => 'tel',
            '#title' => $this->t('Phone number'),
            '#default_value' => $config->get('company_info.phone_number'),
        ];

        $form['email'] = [
            '#type' => 'email',
            '#title' => $this->t('Email address'),
            '#default_value' => $config->get('company_info.email'),
        ];

        $form['description'] = [
            '#type' => 'textarea',
            '#title' => $this->t('Company description'),
            '#default_value' => $config->get('company_info.description'),
        ];

        $form['facebook_link'] = [
            '#type' => 'url',
            '#title' => $this->t('Facebook URL'),
            '#default_value' => $config->get('company_info.facebook_link'),
        ];

        $form['linkedin_link'] = [
            '#type' => 'url',
            '#title' => $this->t('LinkedIn URL'),
            '#default_value' => $config->get('company_info.linkedin_link'),
        ];

        $form['twitter_link'] = [
            '#type' => 'url',
            '#title' => $this->t('Twitter (not X) URL'),
            '#default_value' => $config->get('company_info.twitter_link'),
        ];

        return $form;
    }

    /**
    * {@inheritdoc}
    */
    public function validateForm(array &$form, FormStateInterface $form_state) {
        // Phone number verification
        if (strlen($form_state->getValue('phone_number')) < 3) {
            $form_state->setErrorByName('phone_number', $this->t('The phone number is too short. Please enter a full phone number.'));
        }
    }

    /**
    * {@inheritdoc}
    */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $config = $this->config('company_info.settings');
        $config->set('company_info.name', $form_state->getValue('name'));
        $config->set('company_info.address', $form_state->getValue('address'));
        $config->set('company_info.phone_number', $form_state->getValue('phone_number'));
        $config->set('company_info.email', $form_state->getValue('email'));
        $config->set('company_info.description', $form_state->getValue('description'));
        $config->set('company_info.facebook_link', $form_state->getValue('facebook_link'));
        $config->set('company_info.linkedin_link', $form_state->getValue('linkedin_link'));
        $config->set('company_info.twitter_link', $form_state->getValue('twitter_link'));
        $config->save();
        \Drupal::service('cache_tags.invalidator')->invalidateTags(['company_info_settings']);
        return parent::submitForm($form, $form_state);
    }

}