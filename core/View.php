<?php

/**
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author    Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright 31/05/2019 Vagner Cardoso
 */

namespace Core {

    use Slim\Http\StatusCode;

    /**
     * Class View
     *
     * @package Core
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
         * @param array $options
         *
         * @throws \Twig\Error\LoaderError
         */
        public function __construct($path, array $options = [])
        {
            $this->loader = $this->createLoader(is_string($path) ? [$path] : $path);
            $this->environment = new \Twig\Environment($this->loader, $options);
        }

        /**
         * @param array $paths
         *
         * @return \Twig\Loader\FilesystemLoader
         * @throws \Twig\Error\LoaderError
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

        /**
         * @param \Slim\Http\Response $response
         * @param string $template
         * @param array $context
         *
         * @param int $statusCode
         *
         * @return \Slim\Http\Response
         */
        public function render(\Slim\Http\Response $response, string $template, array $context = [], int $statusCode = StatusCode::HTTP_OK)
        {
            if ($statusCode) {
                $response = $response->withStatus($statusCode);
            }

            $response->getBody()->write(
                $this->fetch($template, $context)
            );

            return $response;
        }

        /**
         * @param string $template
         * @param array $context
         *
         * @return string
         */
        public function fetch(string $template, array $context = [])
        {
            if (substr($template, -5) === '.twig') {
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
         * @param \Twig\Extension\ExtensionInterface
         *
         * @return $this
         */
        public function addExtension(\Twig\Extension\ExtensionInterface $extension)
        {
            $this->environment->addExtension($extension);

            return $this;
        }

        /**
         * @param string $name
         * @param callable $callable
         * @param array $options
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
         * @param string $name
         * @param callable $callable
         * @param array $options
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
         * Get instanceof ViewProvider
         *
         * @return \Twig\Environment
         */
        public function getEnvironment()
        {
            return $this->environment;
        }
    }
}
