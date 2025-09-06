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

    <style>
        /* Custom styles for enhanced design */
        .lucide {
            display: inline-block;
            width: 1em;
            height: 1em;
            stroke-width: 2;
        }
        
        /* Gradient backgrounds */
        .gradient-bg {
            background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%);
        }
        
        .gradient-section {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        }
        
        /* Card hover effects */
        .feature-card {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border-left-color: #3b82f6;
        }
        
        /* Animated elements */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-fade-in {
            animation: fadeIn 0.8s ease-out forwards;
        }
        
        /* Custom button styles */
        .btn-primary {
            background: linear-gradient(90deg, #3b82f6 0%, #2563eb 100%);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(90deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);
        }
        
        .btn-secondary {
            background: transparent;
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background: #f8fafc;
            border-color: #3b82f6;
            transform: translateY(-2px);
        }
        
        /* Dashboard image styling */
        .dashboard-container {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        
        .dashboard-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, rgba(255,255,255,0.1) 0%, rgba(0,0,0,0.2) 100%);
            z-index: 2;
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
        const CheckCircle = () => React.createElement('i', { 'data-lucide': 'check-circle', className: 'lucide w-4 h-4 text-blue-500 mr-2 flex-shrink-0' });
        const BarChart3 = () => React.createElement('i', { 'data-lucide': 'bar-chart-3', className: 'lucide w-6 h-6' });
        const Users = () => React.createElement('i', { 'data-lucide': 'users', className: 'lucide w-6 h-6' });
        const TrendingUp = () => React.createElement('i', { 'data-lucide': 'trending-up', className: 'lucide w-6 h-6' });

        function App() {
            const features = [
                {
                    icon: React.createElement(Warehouse, null),
                    title: "Warehouses",
                    description: "Multi-warehouse management system with real-time inventory tracking across locations"
                },
                {
                    icon: React.createElement(Package, null),
                    title: "Inventory",
                    items: ["Items", "Item Groups", "Inventory Adjustments", "Transfer Orders", "Stock Alerts", "Batch Tracking"]
                },
                {
                    icon: React.createElement(ShoppingCart, null),
                    title: "Sales",
                    items: ["Customers", "Quotations", "Sales Orders", "Packages", "Shipments", "Invoices", "Sales Receipts", "Payments Received", "Credit Notes", "Sales Returns"]
                },
                {
                    icon: React.createElement(CreditCard, null),
                    title: "Purchases",
                    items: ["Vendors", "Purchase Orders", "Purchase Receives", "Bills", "Payments Made", "Vendor Credits", "Shipments", "Supplier Management"]
                },
                {
                    icon: React.createElement(BarChart3, null),
                    title: "Analytics",
                    items: ["Real-time Reports", "Inventory Forecasting", "Sales Analytics", "Performance Dashboards", "Custom Reports"]
                },
                {
                    icon: React.createElement(Settings, null),
                    title: "Settings",
                    items: ["Users", "Roles & Permissions", "Company Settings", "Integration Settings"]
                }
            ];

            const stats = [
                { value: "45%", label: "Reduction in operational costs" },
                { value: "3.5x", label: "Faster inventory turnover" },
                { value: "99.8%", label: "Order accuracy rate" },
                { value: "24/7", label: "Real-time tracking" }
            ];

            const trustedBy = [
                "Hermes Engineering",
                "Wimusani Enterprise Limited",
                "Kany Consulting Group (KCG)",
                "Logistics Plus Inc.",
                "Global Supply Chain Solutions",
                "Omni Distribution Network"
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
                                React.createElement('div', { className: 'bg-blue-100 p-2 rounded-lg mr-3' },
                                    React.createElement(Warehouse, { className: 'w-6 h-6 text-blue-600' })
                                ),
                                React.createElement('span', { className: 'text-xl font-bold text-gray-900' }, 'Supply Catena')
                            ),
                            React.createElement('div', { className: 'flex space-x-4' },
                                React.createElement('button', {
                                    onClick: handleContact,
                                    className: 'text-gray-600 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors'
                                }, 'Contact'),
                                React.createElement('button', {
                                    onClick: handleGetStarted,
                                    className: 'btn-primary text-white px-4 py-2 rounded-md text-sm font-medium shadow-sm'
                                }, 'Get Started')
                            )
                        )
                    )
                ),

                // Hero Section
                React.createElement('section', { className: 'gradient-bg text-white pt-16 pb-32' },
                    React.createElement('div', { className: 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8' },
                        React.createElement('div', { className: 'flex flex-col lg:flex-row items-center justify-between' },
                            React.createElement('div', { className: 'lg:w-1/2 mb-12 lg:mb-0 animate-fade-in' },
                                React.createElement('h1', { className: 'text-4xl md:text-5xl lg:text-6xl font-bold mb-6 leading-tight' },
                                    'Streamline Your Supply Chain Operations',
                                    React.createElement('span', { className: 'block text-blue-200 text-2xl md:text-3xl mt-4 font-normal' }, 'Multi-Warehouse ERP Solution')
                                ),
                                React.createElement('p', { className: 'text-xl text-blue-100 mb-8 max-w-2xl' },
                                    'Optimize inventory management, automate processes, and gain real-time visibility across all your warehouses with our powerful ERP platform.'
                                ),
                                React.createElement('div', { className: 'flex flex-col sm:flex-row gap-4' },
                                    React.createElement('button', {
                                        onClick: handleGetStarted,
                                        className: 'btn-primary text-white px-8 py-4 rounded-lg text-lg font-semibold flex items-center justify-center gap-2 shadow-lg'
                                    }, 'Get Started', React.createElement(ArrowRight, null)),
                                    React.createElement('button', {
                                        onClick: handleContact,
                                        className: 'btn-secondary text-white px-8 py-4 rounded-lg text-lg font-semibold flex items-center justify-center gap-2'
                                    }, React.createElement(Mail, null), 'Contact Us')
                                )
                            ),
                            React.createElement('div', { className: 'lg:w-1/2 dashboard-container animate-fade-in' },
                                React.createElement('img', { 
                                    src: '/storage/pc.jpeg', 
                                    alt: 'Supply Catena Dashboard', 
                                    className: 'w-full h-auto object-cover relative z-1'
                                })
                            )
                        )
                    )
                ),

                // Stats Section
                React.createElement('section', { className: 'py-16 bg-white -mt-24 relative z-10' },
                    React.createElement('div', { className: 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8' },
                        React.createElement('div', { className: 'grid grid-cols-2 md:grid-cols-4 gap-8' },
                            stats.map((stat, index) =>
                                React.createElement('div', { 
                                    key: index,
                                    className: 'text-center p-6 bg-gray-50 rounded-xl shadow-sm'
                                },
                                    React.createElement('div', { className: 'text-3xl font-bold text-blue-600 mb-2' }, stat.value),
                                    React.createElement('div', { className: 'text-gray-600' }, stat.label)
                                )
                            )
                        )
                    )
                ),

                // Features Section
                React.createElement('section', { className: 'py-20 gradient-section' },
                    React.createElement('div', { className: 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8' },
                        React.createElement('div', { className: 'text-center mb-16' },
                            React.createElement('h2', { className: 'text-3xl md:text-4xl font-bold text-gray-900 mb-4' }, 'Comprehensive Supply Chain Management'),
                            React.createElement('p', { className: 'text-xl text-gray-600 max-w-3xl mx-auto' }, 'Everything you need to manage your warehouses, inventory, sales, and purchases in one integrated platform.')
                        ),
                        React.createElement('div', { className: 'grid md:grid-cols-2 lg:grid-cols-3 gap-8' },
                            features.map((feature, index) =>
                                React.createElement('div', {
                                    key: index,
                                    className: 'feature-card bg-white p-6 rounded-xl shadow-sm'
                                },
                                    React.createElement('div', { className: 'flex items-center mb-4' },
                                        React.createElement('div', { className: 'bg-blue-100 p-3 rounded-lg text-blue-600 mr-4' }, feature.icon),
                                        React.createElement('h3', { className: 'text-xl font-semibold text-gray-900' }, feature.title)
                                    ),
                                    feature.description && React.createElement('p', { className: 'text-gray-600 mb-4' }, feature.description),
                                    feature.items && React.createElement('ul', { className: 'space-y-3' },
                                        feature.items.slice(0, 5).map((item, itemIndex) =>
                                            React.createElement('li', { key: itemIndex, className: 'flex items-center text-gray-600' },
                                                React.createElement(CheckCircle, null),
                                                item
                                            )
                                        ),
                                        feature.items.length > 5 && React.createElement('li', { className: 'text-sm text-blue-600 font-medium ml-6 mt-2' },
                                            `+${feature.items.length - 5} more features`
                                        )
                                    )
                                )
                            )
                        )
                    )
                ),

                // Trusted By Section
                React.createElement('section', { className: 'py-16 bg-white' },
                    React.createElement('div', { className: 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8' },
                        React.createElement('h3', { className: 'text-lg font-medium text-gray-500 text-center mb-12' }, 'Trusted by industry leaders worldwide'),
                        React.createElement('div', { className: 'grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-8 items-center' },
                            trustedBy.map((company, index) =>
                                React.createElement('div', { 
                                    key: index, 
                                    className: 'text-gray-700 font-medium text-center p-4 bg-gray-50 rounded-lg hover:shadow-md transition-shadow' 
                                }, company)
                            )
                        )
                    )
                ),

                // Contact Section
                React.createElement('section', { id: 'contact', className: 'py-20 gradient-bg text-white' },
                    React.createElement('div', { className: 'max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center' },
                        React.createElement('h2', { className: 'text-3xl font-bold mb-6' }, 'Ready to transform your supply chain?'),
                        React.createElement('p', { className: 'text-xl text-blue-100 mb-10 max-w-2xl mx-auto' },
                            'Join thousands of companies that have optimized their operations with Supply Catena.'
                        ),
                        React.createElement('div', { className: 'flex flex-col sm:flex-row gap-4 justify-center' },
                            React.createElement('button', {
                                onClick: handleContact,
                                className: 'bg-white text-blue-600 px-8 py-4 rounded-lg text-lg font-semibold hover:bg-blue-50 transition-colors flex items-center justify-center gap-2 shadow-sm'
                            }, React.createElement(Mail, null), 'Contact Sales'),
                            React.createElement('button', {
                                onClick: handleGetStarted,
                                className: 'border border-white text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-white hover:text-blue-600 transition-colors'
                            }, 'Get Free Demo')
                        )
                    )
                ),

                // Footer
                React.createElement('footer', { className: 'bg-gray-900 text-white py-12' },
                    React.createElement('div', { className: 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8' },
                        React.createElement('div', { className: 'flex flex-col md:flex-row justify-between items-center' },
                            React.createElement('div', { className: 'flex items-center mb-6 md:mb-0' },
                                React.createElement('div', { className: 'bg-blue-600 p-2 rounded-lg mr-3' },
                                    React.createElement(Warehouse, { className: 'w-6 h-6 text-white' })
                                ),
                                React.createElement('span', { className: 'text-xl font-bold' }, 'Supply Catena')
                            ),
                            React.createElement('div', { className: 'text-gray-400 text-center md:text-right' },
                                React.createElement('p', { className: 'mb-2' }, 'Multi-Warehouse ERP System for Industrial Efficiency'),
                                React.createElement('p', { className: 'text-sm' }, '© 2023 Supply Catena. All rights reserved.')
                            )
                        )
                    )
                )
            );
        }

        const container = document.getElementById('root');
        const root = ReactDOM.createRoot(container);
        root.render(React.createElement(App));
        
        // Re-initialize Lucide icons after React render
        setTimeout(() => {
            lucide.createIcons();
        }, 100);
    </script>
</body>
</html>
