<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
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
     * @param array        $options
     *
     * @throws \Twig\Error\LoaderError
     */
    public function __construct($path, array $options = [])
    {
        $this->loader = $this->createLoader(is_string($path) ? [$path] : $path);
        $this->environment = new \Twig\Environment($this->loader, $options);
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param string                              $template
     * @param array                               $context
     * @param int                                 $status
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function render(ResponseInterface $response, string $template, array $context = [], ?int $status = StatusCode::HTTP_OK): ResponseInterface
    {
        if ($status) {
            $response = $response->withStatus($status);
        }

        $response->getBody()->write(
            $this->fetch($template, $context)
        );

        return $response;
    }

    /**
     * @param string $template
     * @param array  $context
     *
     * @return string
     */
    public function fetch(string $template, array $context = [])
    {
        if ('.twig' === substr($template, -5)) {
            $template = substr($template, 0, -5);
        }

        $template = str_replace('.', '/', $template);

        if (count(explode('/', $template)) <= 1) {
            $template = "{$template}/index";
        }

        return $this->environment->render(
            "{$template}.twig", $context
        );
    }

    /**
     * @param \Twig\Extension\ExtensionInterface $extension
     *
     * @return $this
     */
    public function addExtension(\Twig\Extension\ExtensionInterface $extension)
    {
        $this->environment->addExtension($extension);

        return $this;
    }

    /**
     * @param string   $name
     * @param callable $callable
     * @param array    $options
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
     * @param string   $name
     * @param callable $callable
     * @param array    $options
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
     * @param string $name
     * @param mixed  $value
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
     * @param array $paths
     *
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
