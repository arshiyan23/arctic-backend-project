<?php

namespace Drupal\search_logger\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Database\Database;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class SearchLoggerController extends ControllerBase {

  /**
   * Returns top 5 search queries.
   */
  public function topSearches($keyword) {
    // Check if the current user has access.
    if (!$this->currentUser()->hasPermission('access search logger api')) {
      throw new AccessDeniedHttpException();
    }

    $connection = Database::getConnection();
    // Build the query.
    $query = $connection->select('search_logger', 's')
      ->fields('s', ['query']);

    // Add a condition if keyword is provided.
    if (!empty($keyword)) {
      $query->condition('s.query', '%' . $connection->escapeLike($keyword) . '%', 'LIKE');
    }

    $query->addExpression('COUNT(s.query)', 'count_query');
    $query->groupBy('s.query');
    $query->orderBy('count_query', 'DESC');
    $query->range(0, 6);
    
    // Execute the query and fetch results.
    $result = $query->execute()->fetchAllAssoc('query');
    
    // Prepare the response data.
    $response_data = [];
    foreach ($result as $row) {
      $response_data[] = strtolower($row->query);
    }
    
    return new JsonResponse($response_data);
  }

  /**
   * Returns the most recent 5 search queries.
   */
  public function recentSearches($keyword) {
    // Check if the current user has access.
    if (!$this->currentUser()->hasPermission('access search logger api')) {
      throw new AccessDeniedHttpException();
    }

    $connection = Database::getConnection();
    $query = $connection->select('search_logger', 's')
      ->fields('s', ['query'])
      ->orderBy('s.created', 'DESC')
      ->range(0, 6);
    
      // Add a condition if keyword is provided.
    if (!empty($keyword)) {
      $query->condition('s.query', '%' . $connection->escapeLike($keyword) . '%', 'LIKE');
    }

    $result = $query->execute()->fetchAllKeyed(0, 0);

    // Prepare the response data in lowercase.
    $response_data = array_map('strtolower', array_values($result));

    return new JsonResponse(array_values($response_data));
  }
}
