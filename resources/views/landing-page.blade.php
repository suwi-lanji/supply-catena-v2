<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supply Catena - Multi-Warehouse ERP</title>

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- React + ReactDOM + Babel -->
    <script crossorigin src="https://unpkg.com/react@18/umd/react.development.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

    <!-- Initialize Lucide -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });
    </script>

    <style>
        /* Optional: Ensure icons render correctly */
        .lucide {
            display: inline-block;
            width: 1em;
            height: 1em;
            stroke-width: 2;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .pulse-button {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(102, 126, 234, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(102, 126, 234, 0);
            }
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50">
    <div id="root"></div>

    <script type="text/babel">
        // Define icons as components
        const ArrowRight = () => React.createElement('i', { 'data-lucide': 'arrow-right', className: 'lucide w-5 h-5' });
        const Mail = () => React.createElement('i', { 'data-lucide': 'mail', className: 'lucide w-5 h-5' });
        const Warehouse = () => React.createElement('i', { 'data-lucide': 'warehouse', className: 'lucide w-6 h-6' });
        const Package = () => React.createElement('i', { 'data-lucide': 'package', className: 'lucide w-6 h-6' });
        const ShoppingCart = () => React.createElement('i', { 'data-lucide': 'shopping-cart', className: 'lucide w-6 h-6' });
        const CreditCard = () => React.createElement('i', { 'data-lucide': 'credit-card', className: 'lucide w-6 h-6' });
        const Settings = () => React.createElement('i', { 'data-lucide': 'settings', className: 'lucide w-6 h-6' });
        const CheckCircle = () => React.createElement('i', { 'data-lucide': 'check-circle', className: 'lucide w-4 h-4 text-emerald-500 mr-2 flex-shrink-0' });
        const TrendingUp = () => React.createElement('i', { 'data-lucide': 'trending-up', className: 'lucide w-6 h-6' });
        const Users = () => React.createElement('i', { 'data-lucide': 'users', className: 'lucide w-6 h-6' });
        const BarChart = () => React.createElement('i', { 'data-lucide': 'bar-chart-2', className: 'lucide w-6 h-6' });
        const Shield = () => React.createElement('i', { 'data-lucide': 'shield', className: 'lucide w-6 h-6' });

        function App() {
            const features = [
                {
                    icon: React.createElement(Warehouse, null),
                    title: "Multi-Warehouse Management",
                    description: "Seamlessly manage inventory across multiple locations with real-time synchronization and intelligent allocation."
                },
                {
                    icon: React.createElement(Package, null),
                    title: "Advanced Inventory Control",
                    items: ["Smart Stock Alerts", "Batch & Serial Tracking", "Automated Reordering", "Inventory Valuation", "Transfer Optimization"]
                },
                {
                    icon: React.createElement(ShoppingCart, null),
                    title: "Sales & Order Management",
                    items: ["Customer Portal", "Automated Quotations", "Order Fulfillment", "Shipping Integration", "Returns Processing", "Commission Tracking"]
                },
                {
                    icon: React.createElement(CreditCard, null),
                    title: "Procurement & Purchasing",
                    items: ["Vendor Management", "Purchase Automation", "Bill Processing", "Payment Scheduling", "Vendor Performance", "Cost Analysis"]
                },
                {
                    icon: React.createElement(Settings, null),
                    title: "Customizable Settings",
                    items: ["User Roles & Permissions", "Custom Workflows", "API Integrations", "Reporting Templates", "Branding Options"]
                }
            ];

            const stats = [
                { number: "99.9%", label: "Uptime", icon: React.createElement(Shield, null) },
                { number: "3+", label: "Active Companies", icon: React.createElement(Users, null) },
                { number: "24/7", label: "Support", icon: React.createElement(BarChart, null) },
                { number: "30%", label: "Avg. Efficiency Gain", icon: React.createElement(TrendingUp, null) }
            ];

            const trustedBy = [
                "Hermes Engineering",
                "Wimusani Enterprise Limited",
                "Kany Consulting Group (KCG)"
            ];

            const handleGetStarted = () => {
                document.getElementById('contact')?.scrollIntoView({ behavior: 'smooth' });
            };

            const handleContact = () => {
                window.location.href = 'mailto:suwilanjichipofya@inongo.space';
            };

            return React.createElement('div', { className: 'min-h-screen bg-gray-50' },

                // Navigation
                React.createElement('nav', { className: 'bg-white shadow-sm sticky top-0 z-50' },
                    React.createElement('div', { className: 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8' },
                        React.createElement('div', { className: 'flex justify-between items-center h-16' },
                            React.createElement('div', { className: 'flex items-center' },
                                React.createElement(Warehouse, { className: 'w-8 h-8 text-indigo-600 mr-3' }),
                                React.createElement('span', { className: 'text-xl font-bold text-gray-900' }, 'Supply Catena')
                            ),
                            React.createElement('div', { className: 'flex space-x-4' },
                                React.createElement('button', {
                                    onClick: handleContact,
                                    className: 'text-gray-600 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium transition-colors'
                                }, 'Contact'),
                                React.createElement('button', {
                                    onClick: handleContact,
                                    className: 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:from-indigo-700 hover:to-purple-700 transition-all shadow-md hover:shadow-lg'
                                }, 'Get Started')
                            )
                        )
                    )
                ),

                // Hero Section with Image
                React.createElement('section', { className: 'relative py-20 overflow-hidden' },
                    React.createElement('div', { className: 'absolute inset-0 bg-gradient-to-br from-indigo-50 to-purple-50' }),
                    React.createElement('div', { className: 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10' },
                        React.createElement('div', { className: 'grid grid-cols-1 lg:grid-cols-2 gap-12 items-center' },
                            // Text Content
                            React.createElement('div', { className: 'text-left' },
                                React.createElement('h1', { className: 'text-4xl md:text-6xl font-bold text-gray-900 mb-6 leading-tight' },
                                    'Revolutionize Your',
                                    React.createElement('span', { className: 'block text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600 mt-2' }, 'Warehouse Operations')
                                ),
                                React.createElement('p', { className: 'text-xl text-gray-600 mb-8 max-w-2xl' },
                                    'Multi-warehouse ERP system designed to optimize inventory, streamline sales, and automate procurement for industrial-scale efficiency.'
                                ),
                                React.createElement('div', { className: 'flex flex-col sm:flex-row gap-4' },
                                    React.createElement('button', {
                                        onClick: handleContact,
                                        className: 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-8 py-4 rounded-lg text-lg font-medium hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg hover:shadow-xl pulse-button'
                                    }, 'Get Started Free', React.createElement(ArrowRight, { className: 'ml-2' })),
                                    React.createElement('button', {
                                        onClick: handleContact,
                                        className: 'border-2 border-indigo-600 text-indigo-600 px-8 py-4 rounded-lg text-lg font-medium hover:bg-indigo-50 transition-colors flex items-center justify-center gap-2'
                                    }, React.createElement(Mail, { className: 'mr-2' }), 'Schedule Demo')
                                )
                            ),
                            // Image
                            React.createElement('div', { className: 'relative' },
                                React.createElement('div', { className: 'absolute -inset-1 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg blur opacity-20' }),
                            )
                        )
                    )
                ),

                // Stats Section
                React.createElement('section', { className: 'py-16 bg-white' },
                    React.createElement('div', { className: 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8' },
                        React.createElement('div', { className: 'grid grid-cols-2 md:grid-cols-4 gap-8' },
                            stats.map((stat, index) =>
                                React.createElement('div', { key: index, className: 'text-center' },
                                    React.createElement('div', { className: 'flex justify-center mb-2 text-indigo-600' }, stat.icon),
                                    React.createElement('div', { className: 'text-3xl md:text-4xl font-bold text-gray-900 mb-1' }, stat.number),
                                    React.createElement('div', { className: 'text-gray-600 font-medium' }, stat.label)
                                )
                            )
                        )
                    )
                ),

                // Features Section
                React.createElement('section', { className: 'py-20 bg-gray-50' },
                    React.createElement('div', { className: 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8' },
                        React.createElement('div', { className: 'text-center mb-16' },
                            React.createElement('h2', { className: 'text-4xl font-bold text-gray-900 mb-4' }, 'Complete ERP Solution'),
                            React.createElement('p', { className: 'text-xl text-gray-600 max-w-3xl mx-auto' }, 'Everything you need to manage your multi-warehouse operations in one powerful platform.')
                        ),
                        React.createElement('div', { className: 'grid md:grid-cols-2 lg:grid-cols-3 gap-8' },
                            features.map((feature, index) =>
                                React.createElement('div', {
                                    key: index,
                                    className: 'bg-white p-8 rounded-xl shadow-sm feature-card transition-all duration-300 border border-gray-100 hover:border-indigo-200'
                                },
                                    React.createElement('div', { className: 'flex items-center mb-6' },
                                        React.createElement('div', { className: 'p-3 bg-indigo-100 rounded-lg text-indigo-600 mr-4' }, feature.icon),
                                        React.createElement('h3', { className: 'text-xl font-semibold text-gray-900' }, feature.title)
                                    ),
                                    feature.description && React.createElement('p', { className: 'text-gray-600 mb-6' }, feature.description),
                                    feature.items && React.createElement('ul', { className: 'space-y-3' },
                                        feature.items.slice(0, 4).map((item, itemIndex) =>
                                            React.createElement('li', { key: itemIndex, className: 'flex items-start text-sm text-gray-700' },
                                                React.createElement(CheckCircle, null),
                                                React.createElement('span', { className: 'ml-1' }, item)
                                            )
                                        ),
                                        feature.items.length > 4 && React.createElement('li', { className: 'text-sm text-indigo-600 font-medium ml-6 mt-2' },
                                            `+${feature.items.length - 4} more advanced features`
                                        )
                                    )
                                )
                            )
                        )
                    )
                ),

                // Trusted By Section
                React.createElement('section', { className: 'py-16 bg-white' },
                    React.createElement('div', { className: 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center' },
                        React.createElement('h3', { className: 'text-lg font-medium text-gray-500 mb-2' }, 'Trusted by Industry Leaders'),
                        React.createElement('p', { className: 'text-2xl font-bold text-gray-900 mb-12' }, ''),
                        React.createElement('div', { className: 'grid md:grid-cols-3 gap-8' },
                            trustedBy.map((company, index) =>
                                React.createElement('div', { key: index, className: 'bg-gray-50 p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow' },
                                    React.createElement('div', { className: 'text-gray-800 font-semibold text-lg' }, company)
                                )
                            )
                        )
                    )
                ),

                // Contact Section
                React.createElement('section', { id: 'contact', className: 'py-20 gradient-bg text-white' },
                    React.createElement('div', { className: 'max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center' },
                        React.createElement('h2', { className: 'text-4xl font-bold mb-6' }, 'Ready to Transform Your Operations?'),
                        React.createElement('p', { className: 'text-xl text-indigo-100 mb-8' },
                            'Join the future of warehouse management. Get started with Supply Catena today and experience unprecedented efficiency.'
                        ),
                        React.createElement('div', { className: 'flex flex-col sm:flex-row gap-4 justify-center' },
                            React.createElement('button', {
                                onClick: handleContact,
                                className: 'bg-white text-indigo-600 px-8 py-4 rounded-lg text-lg font-medium hover:bg-gray-100 transition-colors flex items-center justify-center gap-2 shadow-lg hover:shadow-xl'
                            }, React.createElement(Mail, { className: 'mr-2' }), 'Contact Sales'),
                            React.createElement('button', {
                                onClick: handleContact,
                                className: 'border-2 border-white text-white px-8 py-4 rounded-lg text-lg font-medium hover:bg-white hover:text-indigo-600 transition-colors hover:shadow-lg'
                            }, 'Start Free Trial')
                        ),
                        React.createElement('p', { className: 'text-indigo-200 mt-8 text-sm' }, 'No credit card required • 14-day free trial • Cancel anytime')
                    )
                ),

                // Footer
                React.createElement('footer', { className: 'bg-gray-900 text-white py-12' },
                    React.createElement('div', { className: 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8' },
                        React.createElement('div', { className: 'flex flex-col md:flex-row justify-between items-center' },
                            React.createElement('div', { className: 'flex items-center mb-4 md:mb-0' },
                                React.createElement(Warehouse, { className: 'w-8 h-8 text-indigo-400 mr-3' }),
                                React.createElement('span', { className: 'text-xl font-bold' }, 'Supply Catena')
                            ),
                            React.createElement('div', { className: 'text-center md:text-right' },
                                React.createElement('p', { className: 'text-gray-400' }, '©' + (new Date().getFullYear()) + ' Supply Catena. All rights reserved.'),
                                React.createElement('p', { className: 'text-gray-500 text-sm mt-1' }, 'Multi-Warehouse ERP System for Industrial Efficiency')
                            )
                        )
                    )
                )
            );
        }

        const container = document.getElementById('root');
        const root = ReactDOM.createRoot(container);
        root.render(React.createElement(App));
    </script>
</body>
</html>
