<?php
/**
 * This file is part of Rocketgraph service
 * <http://www.rocketgraph.com>
 */

namespace RG\Command;
use RAM\Interfaces\ConnectorInterface;
use RG\ConnectorService;
use RG\ConsoleCommand;
use Symfony\Component\HttpFoundation\Request;

/**
 * Description of AuthCommand
 *
 * @author K.Christofilos <kostas.christofilos@rocketgraph.com>
 */
class AuthCommand extends ConsoleCommand
{
    /** @var array */
    protected $connectors;

    /** @var ConnectorService */
    protected $connectorService;
    
    public function execute()
    {
        $this->connectorService = $this->container->get('connector_service');
        $this->connectorService->setConnection('live');
        $this->displayAvailableConnectors();
        $this->getConnector();
        $connector = $this->buildConnector();

        $this->authConnector($connector);
    }

    /**
     * Display list of available connectors
     */
    protected function displayAvailableConnectors()
    {
        $this->output('Available Connectors');
        $this->output('--------------------');

        $this->connectors = $this->connectorService->getAvailableConnectors();

        foreach ($this->connectors as $key => $connector) {
            $this->output("$key ({$connector['name']})");
        }
        $this->output();
    }

    /**
     * Read connector name from input
     */
    protected function getConnector()
    {
        do {
            $connectorName = $this->readline("Select connector: ");
        } while (!isset($this->connectors[$connectorName]));

        $this->setInput(['connectorName' => $connectorName]);
    }

    /**
     * Build a primitive connector
     *
     * @return ConnectorInterface
     */
    protected function buildConnector()
    {
        $connector = $this->connectorService
            ->buildConnector($this->input['connectorName']);

        return $connector;
    }

    /**
     * Authorize process for connector
     *
     * @param ConnectorInterface $connector
     */
    protected function authConnector(ConnectorInterface $connector)
    {
        $clientId = $this->readline("Enter Client ID: ");
        $clientSecret = $this->readline("Enter Client Secret: ");
        $redirectUrl = $this->readline("Enter redirect URL: ");

        if ($connector->getOauthType() === 'oauth2') {
            $scopes = $this->readline("Enter scopes (optional): ");
            if ($scopes !== '') {
                $scopes = explode(',', str_replace(' ', '', $scopes));
            }
        } else {
            $scopes = [];
        }

        $params = [
            'client_key' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_url' => $redirectUrl,
            'scopes' => $scopes
        ];

        try {
            $response = $this->connectorService->requestToken($connector, $params);
            $token = $response->request_token;
            $request = $this->getTokenRequest($connector, $token->request_url);

            if ($connector->getOauthType() === 'oauth1') {
                $params['temp_identifier'] = $token->temp_identifier;
                $params['temp_secret'] = $token->temp_secret;
                $params['oauth_token'] = $request->query->get('oauth_token');
                $params['oauth_verifier'] = $request->query->get('oauth_verifier');
                $auth = $this->connectorService->authorizeToken($connector, $params);

                $this->output("Your access token is: {$auth->token->access_token}");
                $this->output("Your access token secret is : {$auth->token->access_token_secret}");
            } else {
                $params['code'] = $request->query->get('code');
                $auth = $this->connectorService->authorizeToken($connector, $params);

                $this->output("Your access token is: {$auth->token->access_token}");
            }

        } catch (\Exception $ex) {
            $this->output("ERROR: {$ex->getMessage()}");
        }
    }

    /**
     * @param string $callbackUrl
     *
     * @return Request
     */
    protected function buildRequestFromCallback($callbackUrl)
    {
        return Request::create($callbackUrl);
    }

    /**
     * @param ConnectorInterface $connector
     * @param string $redirectUrl
     *
     * @return Request
     */
    protected function getTokenRequest(ConnectorInterface $connector, $requestUrl)
    {
        $this->output('', 2);
        $this->output("Paste the following URL to your browser.");
        $this->output('', 2);
        $this->output($requestUrl);
        $this->output('', 2);
        $this->output("Paste the URL that API redirected you below");
        $this->output();

        do {
            $callbackUrl = $this->readline('Paste URL: ');
            $request = $this->buildRequestFromCallback($callbackUrl);
            if (!$this->isProviderResponse($connector, $request)) {
                $this->output("This is not a valid callback URL");
            }
        } while (!$this->isProviderResponse($connector, $request));

        return $request;
    }

    /**
     * @param ConnectorInterface $connector
     * @param Request $request
     *
     * @return bool
     */
    protected function isProviderResponse(ConnectorInterface $connector, Request $request)
    {
        if ($connector->getOauthType() === 'oauth1') {
            return $request->query->has('oauth_token') && $request->query->has('oauth_verifier');
        } else {
            return $request->query->has('code');
        }
    }
}