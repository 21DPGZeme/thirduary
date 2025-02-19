<?php

/**
 * Provides a form for submitting and managing resumes
 *
 * This form allows users to enter their personal details and multiple 
 * work experiences dynamically. It supports adding and removing 
 * experience entries via AJAX
 */


namespace Drupal\resume\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;

class ResumeForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'resume_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $submission_id = NULL) {
    $form['#prefix'] = '<div id="form-wrapper">';
    $form['#suffix'] = '</div>';

    $form['return'] = [
      '#type' => 'link',
      '#title' => $this->t('Return to Submissions List'),
      '#url' => Url::fromRoute('resume.submission_list'),
      '#attributes' => ['class' => ['button']],
    ];
  
    $resume_data = [];
    if ($submission_id && !$form_state->has('work_experience_data')) {
      $connection = \Drupal::database();

      $resume_data = $connection->select('resume_submission', 'r')
        ->fields('r', ['full_name', 'email', 'phone_number'])
        ->condition('r.id', $submission_id)
        ->execute()
        ->fetchAssoc();
    
      $work_experiences = $connection->select('resume_work_experience', 'w')
        ->fields('w', ['id', 'company_name', 'job_title', 'start_date', 'end_date', 'job_description'])
        ->condition('w.submission_id', $submission_id)
        ->execute()
        ->fetchAllAssoc('id');
    
      $work_experiences = array_values($work_experiences);
    
      foreach ($work_experiences as &$experience) {
        $experience = (array) $experience;
        if (!empty($experience['start_date'])) {
          $experience['start_date'] = date('Y-m-d', $experience['start_date']);
        }
        if (!empty($experience['end_date'])) {
          $experience['end_date'] = date('Y-m-d', $experience['end_date']);
        }
      }
      unset($experience);
    
      $form_state->set('job_experience_count', count($work_experiences));
      $form_state->set('work_experience_data', $work_experiences);
    }    
  
    // Static Fields
    $form['full_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Full Name'),
      '#required' => TRUE,
      '#default_value' => $resume_data['full_name'] ?? '',
    ];
  
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#required' => TRUE,
      '#default_value' => $resume_data['email'] ?? '',
    ];
  
    $form['phone_number'] = [
      '#type' => 'tel',
      '#title' => $this->t('Phone Number'),
      '#default_value' => $resume_data['phone_number'] ?? '',
    ];
  
    $job_experience_count = $form_state->get('job_experience_count') ?? 0;
    $work_experience_values = $form_state->get('work_experience_data') ?: [];
  
    $form['work_experience_wrapper'] = [
      '#type' => 'container',
      '#tree' => TRUE,
      '#attributes' => ['id' => 'work-experience-wrapper'],
    ];
  
    for ($i = 0; $i < $job_experience_count; $i++) {
      $experience = $work_experience_values[$i] ?? [];
  
      $form['work_experience_wrapper'][$i] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Work Experience @number', ['@number' => $i + 1]),
      ];
  
      $form['work_experience_wrapper'][$i]['company_name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Company Name'),
        '#required' => TRUE,
        '#default_value' => $experience['company_name'] ?? '',
      ];
  
      $form['work_experience_wrapper'][$i]['job_title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Job Title'),
        '#required' => TRUE,
        '#default_value' => $experience['job_title'] ?? '',
      ];
  
      $form['work_experience_wrapper'][$i]['start_date'] = [
        '#type' => 'date',
        '#title' => $this->t('Start Date'),
        '#required' => TRUE,
        '#default_value' => $experience['start_date'] ?? '',
      ];
  
      $form['work_experience_wrapper'][$i]['end_date'] = [
        '#type' => 'date',
        '#title' => $this->t('End Date'),
        '#description' => $this->t('Leave empty if still employed.'),
        '#default_value' => $experience['end_date'] ?? '',
      ];
  
      $form['work_experience_wrapper'][$i]['job_description'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Job Description'),
        '#default_value' => $experience['job_description'] ?? '',
      ];

      // Remove experience button
      $form['work_experience_wrapper'][$i]['remove_experience'] = [
        '#type' => 'submit',
        '#value' => $this->t('Remove'),
        '#name' => 'remove_experience_' . $i,
        '#submit' => ['::removeExperienceCallback'],
        '#limit_validation_errors' => [],
        '#ajax' => [
          'callback' => '::ajaxRefreshWorkExperience',
          'wrapper' => 'work-experience-wrapper',
        ],
        '#attributes' => ['data-delta' => $i],
      ];      
    }

    // Add experience button
    $form['add_experience'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Experience'),
      '#submit' => ['::addExperienceCallback'],
      '#limit_validation_errors' => [],
      '#ajax' => [
        'callback' => '::ajaxRefreshWorkExperience',
        'wrapper' => 'work-experience-wrapper',
      ],
    ];
    
    // Submit button
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $submission_id ? $this->t('Update Resume') : $this->t('Submit'),
      '#ajax' => [
        'callback' => '::ajaxSubmitCallback',
        'wrapper' => 'form-wrapper',
      ],
    ];
  
    return $form;
  }


  // Refresh the work experience section
  public function ajaxRefreshWorkExperience(array &$form, FormStateInterface $form_state) {
    return $form['work_experience_wrapper'];
  }

  // If validation errors exist, the form will be rebuilt with error messages
  public function ajaxSubmitCallback(array &$form, FormStateInterface $form_state) {
    return $form;
  }  


  // Submit handler for adding a work experience entry
  public function addExperienceCallback(array &$form, FormStateInterface $form_state) {
    $job_experience_count = $form_state->get('job_experience_count') ?: 0;
    $job_experience_count++;
    $form_state->set('job_experience_count', $job_experience_count);

    $existing = $form_state->get('job_experience_data') ?: [];
    $existing[] = [];
    $form_state->set('job_experience_data', $existing);

    $form_state->setRebuild(TRUE);
  }


  // Submit handler for removing a work experience entry
  public function removeExperienceCallback(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $delta_to_remove = $trigger['#attributes']['data-delta'] ?? NULL;

    $input = $form_state->getUserInput();
    $jobs = isset($input['work_experience_wrapper']) ? $input['work_experience_wrapper'] : [];
  
    if (is_array($jobs) && $delta_to_remove !== NULL && isset($jobs[$delta_to_remove])) {
      unset($jobs[$delta_to_remove]);
      $jobs = array_values($jobs);
    }
  
    $form_state->set('job_experience_count', count($jobs));
  
    $input['work_experience_wrapper'] = $jobs;
    $form_state->setUserInput($input);
  
    $form_state->setRebuild(TRUE);
  }
  

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Static field validation
    $full_name = trim($form_state->getValue('full_name'));
    $email = trim($form_state->getValue('email'));
    $phone_number = trim($form_state->getValue('phone_number'));
  
    // Full Name: required and only valid characters
    if (empty($full_name)) {
      $form_state->setErrorByName('full_name', $this->t('Full Name is required.'));
    }
    elseif (!preg_match('/^[a-zA-Z\s\.\'-]+$/', $full_name)) {
      $form_state->setErrorByName('full_name', $this->t('Full Name can only contain letters, spaces, periods, hyphens, and apostrophes.'));
    }
  
    // Email: required and valid
    if (empty($email)) {
      $form_state->setErrorByName('email', $this->t('Email is required.'));
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $form_state->setErrorByName('email', $this->t('Please provide a valid email address.'));
    }
  
    // Phone Number: if provided, must contain valid characters and at least 7 digits
    if (!empty($phone_number)) {
      if (!preg_match('/^[0-9\+\-\s\(\)]+$/', $phone_number)) {
        $form_state->setErrorByName('phone_number', $this->t('Phone number contains invalid characters.'));
      }
      elseif (strlen(preg_replace('/\D/', '', $phone_number)) < 7) {
        $form_state->setErrorByName('phone_number', $this->t('Phone number is too short.'));
      }
    }
  
    // Dynamic field validation
    $jobs = $form_state->getValue('work_experience_wrapper');
    if (is_array($jobs)) {
      foreach ($jobs as $delta => $experience) {
        // Company Name: required
        $company = trim($experience['company_name'] ?? '');
        if (empty($company)) {
          $form_state->setErrorByName("work_experience_wrapper][$delta][company_name", $this->t('Company Name is required for job entry @num.', ['@num' => $delta + 1]));
        }
  
        // Job Title: required
        $job_title = trim($experience['job_title'] ?? '');
        if (empty($job_title)) {
          $form_state->setErrorByName("work_experience_wrapper][$delta][job_title", $this->t('Job Title is required for job entry @num.', ['@num' => $delta + 1]));
        }
  
        // Start Date: required and valid
        $start_date = trim($experience['start_date'] ?? '');
        if (empty($start_date)) {
          $form_state->setErrorByName("work_experience_wrapper][$delta][start_date", $this->t('Start Date is required for job entry @num.', ['@num' => $delta + 1]));
        }
        else {
          $start_timestamp = strtotime($start_date);
          if ($start_timestamp === FALSE) {
            $form_state->setErrorByName("work_experience_wrapper][$delta][start_date", $this->t('Start Date is invalid for job entry @num.', ['@num' => $delta + 1]));
          }
          elseif ($start_timestamp > time()) {
            $form_state->setErrorByName("work_experience_wrapper][$delta][start_date", $this->t('Start Date cannot be in the future for job entry @num.', ['@num' => $delta + 1]));
          }
        }
  
        // End Date: valid
        $end_date = trim($experience['end_date'] ?? '');
        if (!empty($end_date)) {
          $end_timestamp = strtotime($end_date);
          if ($end_timestamp === FALSE) {
            $form_state->setErrorByName("work_experience_wrapper][$delta][end_date", $this->t('End Date is invalid for job entry @num.', ['@num' => $delta + 1]));
          }
          elseif (!empty($start_date) && $start_timestamp !== FALSE && $end_timestamp <= $start_timestamp) {
            $form_state->setErrorByName("work_experience_wrapper][$delta][end_date", $this->t('End Date must be later than Start Date for job entry @num.', ['@num' => $delta + 1]));
          }
        }
  
        // Job description: length check
        $job_description = trim($experience['job_description'] ?? '');
        if (!empty($job_description) && strlen($job_description) > 1000) {
          $form_state->setErrorByName("work_experience_wrapper][$delta][job_description", $this->t('Job Description is too long for job entry @num.', ['@num' => $delta + 1]));
        }
      }
    }
  }
  

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $submission_id = $form_state->getBuildInfo()['args'][0] ?? NULL;
    $connection = \Drupal::database();
  
    if ($submission_id) {
      $connection->update('resume_submission')
        ->fields([
          'full_name' => $form_state->getValue('full_name'),
          'email' => $form_state->getValue('email'),
          'phone_number' => $form_state->getValue('phone_number'),
        ])
        ->condition('id', $submission_id)
        ->execute();
  
      $connection->delete('resume_work_experience')
        ->condition('submission_id', $submission_id)
        ->execute();
    }
    else {
      $submission_id = $connection->insert('resume_submission')
        ->fields([
          'full_name' => $form_state->getValue('full_name'),
          'email' => $form_state->getValue('email'),
          'phone_number' => $form_state->getValue('phone_number'),
          'submitted' => REQUEST_TIME,
        ])
        ->execute();
    }
  
    foreach ($form_state->getValue('work_experience_wrapper') as $experience) {
      $connection->insert('resume_work_experience')
        ->fields([
          'submission_id' => $submission_id,
          'company_name' => $experience['company_name'],
          'job_title' => $experience['job_title'],
          'start_date' => strtotime($experience['start_date']),
          'end_date' => !empty($experience['end_date']) ? strtotime($experience['end_date']) : NULL,
          'job_description' => $experience['job_description'],
        ])
        ->execute();
    }
  
    \Drupal::messenger()->addMessage($this->t('Resume has been successfully saved.'));
  }  
}