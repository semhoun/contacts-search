<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';

/**
 * Instantiate App
 *
 * In order for the factory to work you need to ensure you have installed
 * a supported PSR-7 implementation of your choice e.g.: Slim PSR-7 and a supported
 * ServerRequest creator (included with Slim PSR-7)
 */
$app = AppFactory::create();

/**
  * The routing middleware should be added earlier than the ErrorMiddleware
  * Otherwise exceptions thrown from it will not be handled by the middleware
  */
$app->addRoutingMiddleware();

/**
 * Add Error Middleware
 *
 * @param bool                  $displayErrorDetails -> Should be set to false in production
 * @param bool                  $logErrors -> Parameter is passed to the default ErrorHandler
 * @param bool                  $logErrorDetails -> Display error details in error log
 * @param LoggerInterface|null  $logger -> Optional PSR-3 Logger
 *
 * Note: This middleware should be added last. It will not handle any exceptions/errors
 * for middleware added after it.
 */
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Define app routes
$app->get('/hello/{name}', function (Request $request, Response $response, $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");
    return $response;
});

function getElasticClient():  Elastic\Elasticsearch\Client
{
    $client = Elastic\Elasticsearch\ClientBuilder::create()
		->setHosts(['https://esnode1:9200'])
		->setBasicAuthentication('elastic', getenv('ELASTIC_PASSWORD'))
		->setCABundle(__DIR__ . '/ca.crt')
		->build();
    return $client;
}

$app->get('/create_index', function (Request $request, Response $response, $args) {
	$params = [
		'index' => 'contacts',
		'body' => [
            "mappings" => [
                "properties" => [
                    "externalId" => [
                        "type" => "keyword"
                    ],
                    "address" => [
                        "properties" => [
                            "country" => [
                                "type" => "keyword"
                            ],
                            "city" => [
                                "type" => "keyword"
                            ]
                        ]
                    ],
                    "name" => [
                        "properties" => [
                            "first" => [
                                "type" => "keyword"
                            ],
                            "last" => [
                                "type" => "keyword"
                            ]
                        ]
                    ],
                    "profession" => [
                        "type" => "keyword"
                    ],
                    "info" => [
                        "type" => "text"
                    ],
                    "email" => [
                        "type" => "keyword"
                    ]
                ]
            ]
        ]
    ];
	$res = getElasticClient()->indices()->create($params);
    $response->getBody()->write(print_r($res, true));
    return $response;
});

$app->get('/add_contacts', function (Request $request, Response $response, $args) {
	$response->getBody()->write("Retreiving CVS Contacts<br>\n");

    $curl = curl_init();
	curl_setopt_array($curl, array(
	  CURLOPT_URL => 'https://api.extendsclass.com/csv-generator/templates/5sfxfatlm/generate',
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => 'POST',
	  CURLOPT_HTTPHEADER => array(
		'api-key: ' . getenv('EXTENDCLASS_API_KEY')
	  ),
	));
	$dataCSV = curl_exec($curl);
	curl_close($curl);

    $response->getBody()->write("Importing Contacts<br>\n");

    $importResult = [ 'success' => 0, 'error' => 0];

    $client = getElasticClient();

    $params = ['body' => []];
    $i = 0;
    foreach(preg_split("/((\r?\n)|(\r\n?))/", $dataCSV) as $line){
        $row = str_getcsv($line);
        if ($row[0] == 'id') continue;
        $i++;
        $params['body'][] = [
            'index' => [
                '_index' => 'contacts',
                '_id'    => 'demo' . $row[0]
            ]
        ];

        $params['body'][] = [
            'externalId' => $row[0],
            'name' => [
                'first' => $row[1],
                'last' => $row[2]
            ],
            'email' => [
                $row[3],
                $row[4]
            ],
            'profession' => $row[5],
            'address' => [
                'country' => $row[6],
                'city' => $row[7]
            ],
            'info' => $row[8]
        ];

        // Every 1000 documents stop and send the bulk request
        if ($i % 1000 == 0) {
            $responses = $client->bulk($params);
            $params = ['body' => []];
            unset($responses);
        }
    }

    if (!empty($params['body'])) {
        $responses = $client->bulk($params);
        unset($responses);
    }


    $response->getBody()->write("Import done");
    return $response;
});

$app->get('/api/all', function (Request $request, Response $response, $args) {
    $params = [
        'index' => 'contacts',
        'size'  => 25
    ];

    $data = getElasticClient()->search($params);
    $payload = json_encode($data['hits']['hits']);
    $response->getBody()->write($payload);
    return $response
        ->withHeader('Content-Type', 'application/json');
});

$app->get('/api/search', function (Request $request, Response $response, $args) {
    $qparam = $request->getQueryParams();
    error_log(print_r($qparam, true));
    $params = [
        'index' => 'contacts',
        'size'  => 25,
        'body' => [
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'query_string' => [
                                'query' => $qparam['text'] . '*',
                                'fields' => [
                                    'address.city',
                                    'address.country',
                                    'email',
                                    'name.first',
                                    'name.last'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];

    if (array_key_exists('professions', $qparam)) {
      $params['body']['query']['bool']['must'][] = [
          'terms' => [
              'profession' => $qparam['professions']
          ]
      ];
    }

    $data = getElasticClient()->search($params);
    $payload = json_encode($data['hits']['hits']);
    error_log($payload);
    $response->getBody()->write($payload);
    return $response
        ->withHeader('Content-Type', 'application/json');
});

// Run app
$app->run();
