<?php

namespace Drupal\resume\Service;

use Drupal\Core\Database\Connection;

class ResumeService {

  /**
   * The database connection
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a ResumeService object
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
    * Loads all resume submissions
    *
    * @return array
    *   An array of submission records
    */
    public function loadAllSubmissions() {
      $query = $this->database->select('resume_submission', 'rs')
        ->fields('rs', ['id', 'full_name', 'email', 'phone_number']);
      return $query->execute()->fetchAll();
  }

  /**
   * Loads submission data
   *
   * @param int $submission_id
   *   The submission ID
   *
   * @return array|null
   *   The resume data or NULL if not found
   */
  public function loadSubmission($submission_id) {
    return $this->database->select('resume_submission', 'r')
      ->fields('r', ['full_name', 'email', 'phone_number'])
      ->condition('r.id', $submission_id)
      ->execute()
      ->fetchAssoc();
  }

  /**
   * Loads work experiences for a given submission
   *
   * @param int $submission_id
   *   The submission ID
   *
   * @return array
   *   An array of work experience records
   */
  public function loadWorkExperiences($submission_id) {
    $experiences = $this->database->select('resume_work_experience', 'w')
      ->fields('w', ['id', 'company_name', 'job_title', 'start_date', 'end_date', 'job_description'])
      ->condition('w.submission_id', $submission_id)
      ->execute()
      ->fetchAllAssoc('id');
    return array_values($experiences);
  }

  /**
   * Inserts a new resume submission
   *
   * @param array $fields
   *   An associative array of submission fields.
   *
   * @return int
   *   The new submission ID.
   */
  public function insertSubmission(array $fields) {
    return $this->database->insert('resume_submission')
      ->fields($fields)
      ->execute();
  }

  /**
   * Inserts a new work experience record
   *
   * @param array $fields
   *   An associative array of work experience fields
   *
   * @return int
   *   The new work experience record ID
   */
  public function insertWorkExperience(array $fields) {
    return $this->database->insert('resume_work_experience')
      ->fields($fields)
      ->execute();
  }

  /**
   * Updates an existing resume submission
   *
   * @param int $submission_id
   *   The submission ID to update.
   * @param array $fields
   *   An associative array of fields to update.
   *
   * @return int
   *   The number of updated rows.
   */
  public function updateSubmission($submission_id, array $fields) {
    return $this->database->update('resume_submission')
      ->fields($fields)
      ->condition('id', $submission_id)
      ->execute();
  }

  /**
   * Deletes all work experience records for a given submission
   *
   * @param int $submission_id
   *   The submission ID for which to delete work experiences
   *
   * @return int
   *   The number of deleted rows
   */
  public function deleteWorkExperiences($submission_id) {
    return $this->database->delete('resume_work_experience')
      ->condition('submission_id', $submission_id)
      ->execute();
  }
}