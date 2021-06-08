<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 08/06/2021 Vagner Cardoso
 */

namespace Core;

use Slim\Http\Response;
use Slim\Http\StatusCode;
use Twig\Environment;
use Twig\Extension\ExtensionInterface;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\RuntimeLoaderInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;

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
        $this->loader = $this->createLoader($path);
        $this->environment = new Environment($this->loader, $options);
    }

    /**
     * @param string|array $path
     *
     * @throws \Twig\Error\LoaderError
     *
     * @return \Twig\Loader\FilesystemLoader
     */
    private function createLoader($path): FilesystemLoader
    {
        $paths = is_string($path) ? [$path] : $path;
        $loader = new FilesystemLoader();

        foreach ($paths as $namespace => $location) {
            if (is_string($namespace)) {
                $loader->setPaths($location, $namespace);
            } else {
                $loader->addPath($location);
            }
        }

        return $loader;
    }

    /**
     * @param Response $response
     * @param string   $template
     * @param array    $context
     * @param int      $status
     *
     * @return Response
     */
    public function render(
        Response $response,
        string $template,
        array $context = [],
        int $status =
        StatusCode::HTTP_OK
    ): Response {
        $response->getBody()->write($this->fetch($template, $context));

        return $response->withStatus($status);
    }

    /**
     * @param string $template
     * @param array  $context
     *
     * @return string
     */
    public function fetch(string $template, array $context = []): string
    {
        $removedExtension = $this->removeExtension($template);

        if ($this->exists("{$removedExtension}/index.twig")) {
            $template = sprintf('%s/%s', $removedExtension, 'index');
        }

        $template = $this->normalizeExtension($template);

        return $this->environment->render($template, $context);
    }

    /**
     * @param string $template
     *
     * @return string
     */
    private function removeExtension(string $template): string
    {
        return preg_replace('/\.twig$/', '', $template);
    }

    /**
     * @param string $template
     *
     * @return bool
     */
    public function exists(string $template): bool
    {
        return $this->loader->exists($this->normalizeExtension($template));
    }

    /**
     * @param string $template
     *
     * @return string
     */
    private function normalizeExtension(string $template): string
    {
        $template = $this->removeExtension($template);
        $template = str_replace('.', '/', $template);

        return sprintf('%s.%s', $template, 'twig');
    }

    /**
     * @param \Twig\Extension\ExtensionInterface $extension
     *
     * @return $this
     */
    public function addExtension(ExtensionInterface $extension): View
    {
        $this->environment->addExtension($extension);

        return $this;
    }

    /**
     * @param \Twig\RuntimeLoader\RuntimeLoaderInterface $runtimeLoader
     *
     * @return $this
     */
    public function addRuntimeLoader(RuntimeLoaderInterface $runtimeLoader): View
    {
        $this->environment->addRuntimeLoader($runtimeLoader);

        return $this;
    }

    /**
     * @param string   $name
     * @param callable $callable
     * @param array    $options
     *
     * @return $this
     */
    public function addFunction(string $name, $callable, array $options = ['is_safe' => ['all']]): View
    {
        $this->environment->addFunction(new TwigFunction($name, $callable, $options));

        return $this;
    }

    /**
     * @param string   $name
     * @param callable $callable
     * @param array    $options
     *
     * @return $this
     */
    public function addFilter(string $name, $callable, array $options = ['is_safe' => ['all']]): View
    {
        $this->environment->addFilter(new TwigFilter($name, $callable, $options));

        return $this;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function addGlobal(string $name, $value): View
    {
        $this->environment->addGlobal($name, $value);

        return $this;
    }

    /**
     * @return \Twig\Environment
     */
    public function getEnvironment(): Environment
    {
        return $this->environment;
    }

    /**
     * @return \Twig\Loader\FilesystemLoader
     */
    public function getLoader(): FilesystemLoader
    {
        return $this->loader;
    }
}
