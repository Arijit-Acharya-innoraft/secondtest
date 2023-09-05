<?php

namespace Drupal\api_response\Controller;

use Drupal\api_response\Services\StatisticsService;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * A class for exposing content via api.
 */
class ApiController extends ControllerBase {

  /**
   * The EntityTypeManager service.
   *
   * @var Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The statistics service.
   *
   * @var \Drupal\api_response\Services\StatisticsService
   */
  protected $statisticsService;

  /**
   * A class controller.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The EntityTypeManager connection service.
   * @param \Drupal\api_response\Services\StatisticsService $statistics_service
   *   The StatisticsService connection service.
   */
  public function __construct(EntityTypeManager $entity_type_manager, StatisticsService $statistics_service) {
    $this->entityTypeManager = $entity_type_manager;
    $this->statisticsService = $statistics_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('api_response.statistics_service')
    );
  }

  /**
   * Method that implements the api expose .
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request method.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The response.
   */
  public function apiCreation(Request $request) {

    $tag_name = $request->get('tags');
    if (!empty($tag_name)) {
      $node_storage = $this->entityTypeManager->getStorage('node');
      $query = $node_storage->getQuery();
      $nids = $query
        ->condition('type', 'news')
        ->accessCheck(FALSE)
        ->execute();

      $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
      foreach ($nodes as $node) {

        // An array for storing the images.
        $images = [];

        // Checking if the summery field is present or not.
        if (!empty($node->body->summary)) {
          $summery = $node->body->summary;
        }
        else {
          $summery = "No content Found";
        }

        // Storing the images if present.
        $node_images = $node->field_images;
        if ($node_images) {
          foreach ($node_images as $node_image) {
            $string = ($node_image->entity->getFileUri());
            $character = '/';
            $offset = strripos($string, $character, 0);
            $images[] = substr($string, $offset + 1);
          }
        }

        // Getting the taxonomy terms.
        $target_ids = $node->field_news_tags->getValue();
        foreach ($target_ids as $tid) {
          $taxonomy_term_ids[] = $tid['target_id'];
        }
        $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadMultiple($taxonomy_term_ids);
        foreach ($terms as $term) {
          $term_name[] = $term->getName();
        }

        // Getting the node count.
        $view_count = $this->statisticsService->getViewCount($node->nid->value);

        $build[] = [
          'nid' => $node->nid->value,
          $data[] = [
            'title' => $node->getTitle(),
            'body' => $node->body->value,
            'summery' => $summery,
            'images' => $images,
            'tags' => implode(" ,", $term_name),
            'published_date' => $node->field_news_published_date->value,
            'View count of news URL' => $view_count,
          ],
        ];
      }

      return new JsonResponse($build);
    }
    else {
      return [
        '#type' => 'markup',
        '#markup' => $this->t('No news for the Tag was found.'),
      ];
    }

  }

}
