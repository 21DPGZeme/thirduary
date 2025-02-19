<?php

namespace Drupal\resume\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Response;

class ResumeController extends ControllerBase {

  /**
   * Displays resume submissions
   */
  public function listSubmissions() {
    $header = [
      $this->t('#'),
      $this->t('Full Name'),
      $this->t('Email'),
      $this->t('Phone Number'),
      $this->t('Actions'),
    ];

    $query = Database::getConnection()->select('resume_submission', 'rs')
      ->fields('rs', ['id', 'full_name', 'email', 'phone_number'])
      ->execute();

    $rows = [];
    foreach ($query as $record) {
      $rows[] = [
        'id' => $record->id,
        'full_name' => $record->full_name,
        'email' => $record->email,
        'phone_number' => $record->phone_number,
        'edit' => [
          'data' => [
            '#type' => 'link',
            '#title' => $this->t('Edit'),
            '#url' => Url::fromRoute('resume.edit_submission', ['submission_id' => $record->id]),
          ],
        ],
      ];
    }

    return [
      [
        '#type' => 'link',
        '#title' => $this->t('Create New Submission'),
        '#url' => Url::fromRoute('resume.form'),
        '#attributes' => ['class' => ['button', 'button--primary']],
      ],
      [
        '#type' => 'table',
        '#header' => $header,
        '#rows' => $rows,
        '#empty' => $this->t('No submissions found.'),
        '#attributes' => [
          'class' => ['table', 'table-bordered', 'table-striped'],
        ],
        '#attached' => [
          'library' => [
            'resume/resume.table',
          ],
        ],
      ],
    ];
  }
}
