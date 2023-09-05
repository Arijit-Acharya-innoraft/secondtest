<?php

namespace Drupal\api_response\Services;

use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A service class for fetching the node count.
 */
class StatisticsService {
  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a new StatisticsService object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection service.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * Get the view count for a specific node.
   *
   * @param int $nid
   *   The entity ID.
   *
   * @return int|null
   *   The view count or null if not found.
   */
  public function getViewCount($nid) {
    $result = $this->database->select('node_counter', 'nc')
      ->fields('nc', ['totalcount'])
      ->condition('nc.nid', $nid)
      ->execute();

    return $result->fetchField();

  }

}
