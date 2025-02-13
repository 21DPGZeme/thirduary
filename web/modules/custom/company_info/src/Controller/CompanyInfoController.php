<?php

namespace Drupal\company_info\Controller;

use Drupal\Core\Controller\ControllerBase;

class CompanyInfoController extends ControllerBase {
    public function displayInfo() {
        $config = \Drupal::config('company_info.settings');

        $build = [
            '#cache' => [
                'tags' => ['company_info_settings']
            ],
            '#theme' => 'company_info_template',
            '#name' => $config->get('company_info.name'),
            '#address' => $config->get('company_info.address'),
            '#number' => $config->get('company_info.phone_number'),
            '#email' => $config->get('company_info.email'),
            '#description' => $config->get('company_info.description'),
            '#facebook' => $config->get('company_info.facebook_link'),
            '#linkedin' => $config->get('company_info.linkedin_link'),
            '#twitter' => $config->get('company_info.twitter_link'),
        ];

        return $build;
    }
}