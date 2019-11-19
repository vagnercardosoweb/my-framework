<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 19/11/2019 Vagner Cardoso
 */

namespace Core;

use Psr\Http\Message\ResponseInterface;
use Slim\Http\StatusCode;

/**
 * Class View.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class View
{
    /**
     * @var \Twig\Loader\FilesystemLoader
     */
    protected $loader;

    /**
     * @var \Twig\Environment
     */
    protected $environment;

    /**
     * @param string|array $path
     *
     * @throws \Twig\Error\LoaderError
     */
    public function __construct($path, array $options = [])
    {
        $path = is_string($path) ? [$path] : $path;
        array_push($path, ROOT);

        $this->loader = $this->createLoader($path);
        $this->environment = new \Twig\Environment($this->loader, $options);
    }

    public function render(ResponseInterface $response, string $template, array $context = [], int $status = StatusCode::HTTP_OK): ResponseInterface
    {
        if ($status) {
            $response = $response->withStatus($status);
        }

        $response->getBody()->write($this->fetch($template, $context));

        return $response;
    }

    /**
     * @return string
     */
    public function fetch(string $template, array $context = [])
    {
        if ('.twig' === substr($template, -5)) {
            $template = substr($template, 0, -5);
        }

        $template = str_replace('.', '/', $template);

        if (preg_match('/^@.*/i', $template)) {
            list($namespace, $folder) = explode('/', $template, 2);

            $path = $this->loader->getPaths(str_replace('@', '', $namespace));

            if (!empty($path[0]) && file_exists("{$path[0]}/{$folder}/index.twig")) {
                $template = "{$template}/index";
            }
        }

        return $this->environment->render("{$template}.twig", $context);
    }

    /**
     * @return $this
     */
    public function addExtension(\Twig\Extension\ExtensionInterface $extension)
    {
        $this->environment->addExtension($extension);

        return $this;
    }

    /**
     * @param callable $callable
     *
     * @return $this
     */
    public function addFunction(string $name, $callable, array $options = ['is_safe' => ['all']])
    {
        $this->environment->addFunction(
            new \Twig\TwigFunction($name, $callable, $options)
        );

        return $this;
    }

    /**
     * @param callable $callable
     *
     * @return $this
     */
    public function addFilter(string $name, $callable, array $options = ['is_safe' => ['all']])
    {
        $this->environment->addFilter(
            new \Twig\TwigFilter($name, $callable, $options)
        );

        return $this;
    }

    /**
     * @param mixed $value
     *
     * @return $this
     */
    public function addGlobal(string $name, $value)
    {
        $this->environment->addGlobal($name, $value);

        return $this;
    }

    /**
     * Get instanceof ViewProvider.
     *
     * @return \Twig\Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @throws \Twig\Error\LoaderError
     *
     * @return \Twig\Loader\FilesystemLoader
     */
    private function createLoader(array $paths)
    {
        $loader = new \Twig\Loader\FilesystemLoader();

        foreach ($paths as $namespace => $path) {
            if (is_string($namespace)) {
                $loader->setPaths($path, $namespace);
            } else {
                $loader->addPath($path);
            }
        }

        return $loader;
    }
}
