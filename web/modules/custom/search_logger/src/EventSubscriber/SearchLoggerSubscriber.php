<?php
// src/EventSubscriber/SearchLoggerSubscriber.php
namespace Drupal\search_logger\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Class SearchLoggerSubscriber.
 */
class SearchLoggerSubscriber implements EventSubscriberInterface {

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a new SearchLoggerSubscriber object.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct(LoggerChannelFactoryInterface $logger_factory) {
    $this->logger = $logger_factory->get('search_logger');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['logSearchQuery', 30];
    return $events;
  }

  /**
   * Logs the search query.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The event to process.
   */
  public function logSearchQuery(RequestEvent $event) {
    $request = $event->getRequest();
    $path = $request->getPathInfo();

    //$this->logger->debug('Request path: ' . $path);

    if ($path === '/jsonapi/index/artic_index_database' || $path === '/jsonapi/index/artic_index_database/') {
      //$this->logger->debug('Request path 1: ' . print_r($request->query->all(), true));
      $search_query = $request->query->all();
      $search_string = $search_query['filter']['fulltext'];

      // Truncate spaces, new lines, and tabs from the beginning and end
      $search_string = trim($search_string);
      
      // Convert to lowercase
      $search_string = strtolower($search_string);

      // Split the search string into words and check the count
      $words = preg_split('/\s+/', $search_string);
      $word_count = count($words);

      if (!empty($search_string) && $word_count <= 2 && strlen($search_string) > 3) {
        \Drupal::database()->insert('search_logger')
          ->fields(['query' => $search_string, 'created' => time()])
          ->execute();
        //$this->logger->debug('Search query logged.');
      }
    }
  }

}
