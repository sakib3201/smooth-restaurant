<?php

declare(strict_types=1);

namespace SmoothRestaurant\Providers {
    use SmoothRestaurant\Core\Container;
    use SmoothRestaurant\Core\ServiceProvider;

    /**
     * Assets platform provider.
     *
     * Owns the smooth_should_load() gate, reads .asset.php dependency
     * manifests from the @wordpress/scripts build, and enqueues per-surface
     * assets only when the gate passes. Off-Smooth requests enqueue zero
     * Smooth scripts or styles.
     */
    class AssetsProvider extends ServiceProvider
    {
        /**
         * Script/style handle prefix.
         */
        public const HANDLE_PREFIX = 'smooth-';

        /**
         * Public diner surface built to assets/build/frontend/.
         */
        public const FRONTEND_SURFACE = 'frontend';

        /**
         * Owner dashboard surface built to assets/build/admin/.
         */
        public const ADMIN_SURFACE = 'admin';

        /**
         * Smooth blocks that imply a Smooth frontend request.
         *
         * @var list<string>
         */
        public const SMOOTH_BLOCKS = [
            'smooth/menu-grid',
            'smooth/menu-item',
            'smooth/reservation-form',
            'smooth/order-status',
            'smooth/checkout',
        ];

        /**
         * Smooth shortcodes that imply a Smooth frontend request.
         *
         * @var list<string>
         */
        public const SMOOTH_SHORTCODES = [
            'smooth_menu',
            'smooth_reservation',
        ];

        /**
         * Bind services with no side effects.
         *
         * @param Container $container The DI container.
         */
        public function register(Container $container): void
        {
            $container->singleton(self::class, fn (): self => $this);
        }

        /**
         * Boot the provider after all providers are registered.
         *
         * Bails before adding hooks when assets can never be needed
         * (cron). The per-request gate runs inside the enqueue callbacks.
         *
         * @param Container $container The DI container.
         */
        public function boot(Container $container): void
        {
            if ($this->isDoingCron()) {
                return;
            }
            $this->addHook('wp_enqueue_scripts', [$this, 'enqueueFrontend']);
            $this->addHook('admin_enqueue_scripts', [$this, 'enqueueAdmin']);
        }

        /**
         * Asset gate for procedural callers.
         *
         * Backs the global smooth_should_load() function.
         */
        public static function shouldLoadGlobal(): bool
        {
            return (new self(new Container()))->shouldLoad();
        }

        /**
         * Whether Smooth assets should load on the current request.
         */
        public function shouldLoad(): bool
        {
            return $this->filterLoad($this->detectSmoothContext());
        }

        /**
         * Enqueue the diner surface when the gate passes.
         */
        public function enqueueFrontend(): void
        {
            if (!$this->shouldLoad()) {
                return;
            }
            $this->enqueueSurface(self::FRONTEND_SURFACE);
        }

        /**
         * Enqueue the owner surface when the gate passes.
         */
        public function enqueueAdmin(): void
        {
            if (!$this->shouldLoad()) {
                return;
            }
            $this->enqueueSurface(self::ADMIN_SURFACE);
        }

        /**
         * Detect a Smooth request: Smooth admin screen, block, or shortcode.
         */
        protected function detectSmoothContext(): bool
        {
            if ($this->isAdminContext()) {
                return $this->isSmoothAdminScreen();
            }

            return $this->hasSmoothFrontendContent();
        }

        /**
         * Filter the Smooth asset gate decision.
         *
         * @since 0.1.0
         *
         * @param bool $load Whether Smooth assets should load on this request.
         * @return bool
         *
         * @example add_filter( 'smooth_should_load', '__return_true' );
         */
        protected function filterLoad(bool $load): bool
        {
            if (!\function_exists('apply_filters')) {
                return $load;
            }

            return (bool) \apply_filters('smooth_should_load', $load);
        }

        /**
         * Whether the current request runs inside wp-admin.
         */
        protected function isAdminContext(): bool
        {
            if (!\function_exists('is_admin')) {
                return false;
            }

            return \is_admin();
        }

        /**
         * Whether the current admin screen belongs to Smooth.
         */
        protected function isSmoothAdminScreen(): bool
        {
            if (!\function_exists('get_current_screen')) {
                return false;
            }
            $screen = \get_current_screen();
            if (!$screen instanceof \WP_Screen) {
                return false;
            }

            return \str_contains($screen->id, 'smooth');
        }

        /**
         * Whether the current frontend post carries Smooth content.
         */
        protected function hasSmoothFrontendContent(): bool
        {
            if (\function_exists('has_block')) {
                foreach (self::SMOOTH_BLOCKS as $block) {
                    if ((bool) \has_block($block)) {
                        return true;
                    }
                }
            }
            $content = $this->currentPostContent();
            if ('' === $content || !\function_exists('has_shortcode')) {
                return false;
            }
            foreach (self::SMOOTH_SHORTCODES as $tag) {
                if ((bool) \has_shortcode($content, $tag)) {
                    return true;
                }
            }

            return false;
        }

        /**
         * Body of the current frontend post, if any.
         */
        protected function currentPostContent(): string
        {
            if (!\function_exists('get_post')) {
                return '';
            }
            $post = \get_post();
            if ($post instanceof \WP_Post) {
                return $post->post_content;
            }

            return '';
        }

        /**
         * Whether the current request is a cron run.
         */
        protected function isDoingCron(): bool
        {
            if (!\function_exists('wp_doing_cron')) {
                return false;
            }

            return \wp_doing_cron();
        }

        /**
         * Register a WordPress hook when the API is available.
         *
         * @param callable $callback Hook callback.
         */
        protected function addHook(string $hook, callable $callback): void
        {
            if (\function_exists('add_action')) {
                \add_action($hook, $callback);
            }
        }

        /**
         * Register and enqueue one built surface from its .asset.php manifest.
         *
         * Missing manifests (surface not built yet) enqueue nothing.
         */
        protected function enqueueSurface(string $surface): void
        {
            $manifest = $this->manifestData($surface);
            if (null === $manifest) {
                return;
            }
            if (!\function_exists('wp_register_script') || !\function_exists('wp_enqueue_script')) {
                return;
            }
            $handle = self::HANDLE_PREFIX . $surface;
            \wp_register_script(
                $handle,
                $this->assetUrl($surface, 'js'),
                $manifest['dependencies'],
                $manifest['version'],
                true
            );
            \wp_enqueue_script($handle);
            if (!$this->assetFileExists($surface, 'css')) {
                return;
            }
            if (!\function_exists('wp_register_style') || !\function_exists('wp_enqueue_style')) {
                return;
            }
            \wp_register_style($handle, $this->assetUrl($surface, 'css'), [], $manifest['version']);
            \wp_enqueue_style($handle);
        }

        /**
         * Read a surface .asset.php manifest from the script build.
         *
         * @return array{dependencies: list<string>, version: string}|null
         */
        protected function manifestData(string $surface): ?array
        {
            $file = $this->buildDir() . '/' . $surface . '/index.asset.php';
            if (!\is_readable($file)) {
                return null;
            }
            $data = require $file;
            if (!\is_array($data)) {
                return null;
            }
            $dependencies = [];
            if (isset($data['dependencies']) && \is_array($data['dependencies'])) {
                foreach ($data['dependencies'] as $dependency) {
                    $dependencies[] = (string) $dependency;
                }
            }
            $version = isset($data['version']) ? (string) $data['version'] : '0.0.0';

            return ['dependencies' => $dependencies, 'version' => $version];
        }

        /**
         * Absolute build output directory, empty when unresolvable.
         */
        protected function buildDir(): string
        {
            if (!\defined('SR_PLUGIN_DIR')) {
                return '';
            }
            $dir = \constant('SR_PLUGIN_DIR');

            return \is_string($dir) ? $dir . 'assets/build' : '';
        }

        /**
         * Public URL of a built surface file.
         */
        protected function assetUrl(string $surface, string $extension): string
        {
            if (!\defined('SR_PLUGIN_URL')) {
                return '';
            }
            $base = \constant('SR_PLUGIN_URL');

            return \is_string($base) ? $base . 'assets/build/' . $surface . '/index.' . $extension : '';
        }

        /**
         * Whether a built surface file exists.
         */
        protected function assetFileExists(string $surface, string $extension): bool
        {
            $dir = $this->buildDir();
            if ('' === $dir) {
                return false;
            }

            return \is_readable($dir . '/' . $surface . '/index.' . $extension);
        }
    }
}

namespace {
    if (!\function_exists('smooth_should_load')) {
        /**
         * Whether Smooth assets should load on the current request.
         *
         * Owned by the Assets provider: true on Smooth admin screens and on
         * frontend requests carrying Smooth blocks or shortcodes, false
         * everywhere else. Overridable via the smooth_should_load filter.
         *
         * @since 0.1.0
         *
         * @return bool
         *
         * @example $load = smooth_should_load();
         */
        function smooth_should_load(): bool
        {
            return \SmoothRestaurant\Providers\AssetsProvider::shouldLoadGlobal();
        }
    }
}
