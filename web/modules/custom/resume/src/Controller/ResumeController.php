<?php

namespace Drupal\resume\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class ResumeController extends ControllerBase {
  /**
   * The resume storage service.
   *
   * @var \Drupal\resume\Service\ResumeService
   */
  protected $storage;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->storage = $container->get('resume.service');
    return $instance;
  }

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

    $submissions = $this->storage->loadAllSubmissions();

    $rows = [];
    foreach ($submissions as $record) {
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
