<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supply Catena Cloud ERP</title>

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
        const CheckCircle = () => React.createElement('i', { 'data-lucide': 'check-circle', className: 'lucide w-4 h-4 text-gray-400 mr-2 flex-shrink-0' });

        function App() {
            const features = [
                {
                    icon: React.createElement(Warehouse, null),
                    title: "Warehouses",
                    description: "Multi-warehouse management system"
                },
                {
                    icon: React.createElement(Package, null),
                    title: "Inventory",
                    items: ["Items", "Item Groups", "Inventory Adjustments", "Transfer Orders"]
                },
                {
                    icon: React.createElement(ShoppingCart, null),
                    title: "Sales",
                    items: ["Customers", "Quotations", "Sales Orders", "Packages", "Shipments", "Invoices", "Sales Receipts", "Payments Received", "Credit Notes", "Sales Returns"]
                },
                {
                    icon: React.createElement(CreditCard, null),
                    title: "Purchases",
                    items: ["Vendors", "Purchase Orders", "Purchase Receives", "Bills", "Payments Made", "Vendor Credits", "Shipments"]
                },
                {
                    icon: React.createElement(Settings, null),
                    title: "Settings",
                    items: ["Users"]
                }
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
                React.createElement('nav', { className: 'bg-white shadow-sm' },
                    React.createElement('div', { className: 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8' },
                        React.createElement('div', { className: 'flex justify-between items-center h-16' },
                            React.createElement('div', { className: 'flex items-center' },
                                React.createElement(Warehouse, { className: 'w-8 h-8 text-gray-700 mr-3' }),
                                React.createElement('span', { className: 'text-xl font-semibold text-gray-900' }, 'Supply Catena')
                            ),
                            React.createElement('div', { className: 'flex space-x-4' },
                                React.createElement('button', {
                                    onClick: handleContact,
                                    className: 'text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium transition-colors'
                                }, 'Contact'),
                                React.createElement('button', {
                                    onClick: handleGetStarted,
                                    className: 'bg-gray-900 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-800 transition-colors'
                                }, 'Get Started')
                            )
                        )
                    )
                ),

                // Hero Section
                React.createElement('section', { className: 'bg-white py-20' },
                    React.createElement('div', { className: 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center' },
                        React.createElement('h1', { className: 'text-4xl md:text-6xl font-bold text-gray-900 mb-6' },
                            'Multi-Warehouse ERP System',
                            React.createElement('span', { className: 'block text-gray-600 text-2xl md:text-3xl mt-2 font-normal' }, 'for Industrial Efficiency')
                        ),
                        React.createElement('p', { className: 'text-xl text-gray-600 mb-8 max-w-3xl mx-auto' },
                            'Streamline your operations with comprehensive inventory management, sales tracking, and procurement processes across multiple warehouses.'
                        ),
                        React.createElement('div', { className: 'flex flex-col sm:flex-row gap-4 justify-center' },
                            React.createElement('button', {
                                onClick: handleGetStarted,
                                className: 'bg-gray-900 text-white px-8 py-3 rounded-lg text-lg font-medium hover:bg-gray-800 transition-colors flex items-center justify-center gap-2'
                            }, 'Get Started', React.createElement(ArrowRight, null)),
                            React.createElement('button', {
                                onClick: handleContact,
                                className: 'border border-gray-300 text-gray-700 px-8 py-3 rounded-lg text-lg font-medium hover:bg-gray-50 transition-colors flex items-center justify-center gap-2'
                            }, React.createElement(Mail, null), 'Contact Us')
                        )
                    )
                ),

                // Features Section
                React.createElement('section', { className: 'py-20 bg-gray-50' },
                    React.createElement('div', { className: 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8' },
                        React.createElement('h2', { className: 'text-3xl font-bold text-gray-900 text-center mb-12' }, 'Complete ERP Solution'),
                        React.createElement('div', { className: 'grid md:grid-cols-2 lg:grid-cols-3 gap-8' },
                            features.map((feature, index) =>
                                React.createElement('div', {
                                    key: index,
                                    className: 'bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow'
                                },
                                    React.createElement('div', { className: 'flex items-center mb-4' },
                                        React.createElement('div', { className: 'text-gray-700 mr-3' }, feature.icon),
                                        React.createElement('h3', { className: 'text-xl font-semibold text-gray-900' }, feature.title)
                                    ),
                                    feature.description && React.createElement('p', { className: 'text-gray-600' }, feature.description),
                                    feature.items && React.createElement('ul', { className: 'space-y-2' },
                                        feature.items.slice(0, 4).map((item, itemIndex) =>
                                            React.createElement('li', { key: itemIndex, className: 'flex items-center text-sm text-gray-600' },
                                                React.createElement(CheckCircle, null),
                                                item
                                            )
                                        ),
                                        feature.items.length > 4 && React.createElement('li', { className: 'text-sm text-gray-500 ml-6' },
                                            `+${feature.items.length - 4} more features`
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
                        React.createElement('h3', { className: 'text-lg font-medium text-gray-500 mb-8' }, 'Trusted by leading organizations'),
                        React.createElement('div', { className: 'grid md:grid-cols-3 gap-8' },
                            trustedBy.map((company, index) =>
                                React.createElement('div', { key: index, className: 'text-gray-700 font-medium text-lg' }, company)
                            )
                        )
                    )
                ),

                // Contact Section
                React.createElement('section', { id: 'contact', className: 'py-20 bg-gray-900 text-white' },
                    React.createElement('div', { className: 'max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center' },
                        React.createElement('h2', { className: 'text-3xl font-bold mb-6' }, 'Ready to optimize your operations?'),
                        React.createElement('p', { className: 'text-xl text-gray-300 mb-8' },
                            'Get started with Supply Catena and transform your warehouse management today.'
                        ),
                        React.createElement('div', { className: 'flex flex-col sm:flex-row gap-4 justify-center' },
                            React.createElement('button', {
                                onClick: handleContact,
                                className: 'bg-white text-gray-900 px-8 py-3 rounded-lg text-lg font-medium hover:bg-gray-100 transition-colors flex items-center justify-center gap-2'
                            }, React.createElement(Mail, null), 'Contact Sales'),
                            React.createElement('button', {
                                onClick: handleGetStarted,
                                className: 'border border-white text-white px-8 py-3 rounded-lg text-lg font-medium hover:bg-white hover:text-gray-900 transition-colors'
                            }, 'Get Started')
                        )
                    )
                ),

                // Footer
                React.createElement('footer', { className: 'bg-white py-8 border-t border-gray-200' },
                    React.createElement('div', { className: 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center' },
                        React.createElement('div', { className: 'flex items-center justify-center mb-4' },
                            React.createElement(Warehouse, { className: 'w-6 h-6 text-gray-700 mr-2' }),
                            React.createElement('span', { className: 'text-lg font-semibold text-gray-900' }, 'Supply Catena')
                        ),
                        React.createElement('p', { className: 'text-gray-600' }, 'Multi-Warehouse ERP System for Industrial Efficiency')
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
