import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

// Force rebuild
export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/bootstrap-custom.css',
                'resources/css/dark-mode.css',
                'resources/css/public-layout.css',
                'resources/css/accessibility.css',
                'resources/css/home.css',
                'resources/css/pengumuman.css',
                'resources/css/visitor-book.css',
                'resources/css/about.css',
                'resources/css/contact.css',
                'resources/css/admin.css',
                'resources/js/app.js',
                'resources/js/bootstrap-bundle.js',
                'resources/js/chart-bundle.js',
                'resources/js/accessibility.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        host: '127.0.0.1',
        port: 5173,
        strictPort: true,
    },
    build: {
        // Production optimizations
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true, // Remove console.log in production
                drop_debugger: true,
                pure_funcs: ['console.log', 'console.info', 'console.debug'],
            },
        },
        rollupOptions: {
            output: {
                manualChunks(id) {
                    // Separate vendor chunks for better caching
                    if (id.includes('node_modules')) {
                        if (id.includes('bootstrap')) {
                            return 'bootstrap';
                        }
                        if (id.includes('chart.js')) {
                            return 'charts';
                        }
                        if (id.includes('alpinejs')) {
                            return 'alpine';
                        }
                        // Group other vendors
                        return 'vendor';
                    }
                },
            },
        },
        cssCodeSplit: true,
        cssMinify: true,
        reportCompressedSize: false, // Faster builds
        chunkSizeWarningLimit: 1000,
        sourcemap: false, // Disable sourcemaps in production for smaller files
    },
    optimizeDeps: {
        include: [
            'alpinejs',
            'axios',
            'bootstrap',
            'chart.js',
            '@fortawesome/fontawesome-free',
        ],
    },
});
