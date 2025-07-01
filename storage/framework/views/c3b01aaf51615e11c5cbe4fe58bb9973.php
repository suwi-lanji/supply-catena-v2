<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Warehouse</title>
        <link rel="icon" type="icon/png" href="/logo.png"/>
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="antialiased font-sans">

<div class="flex flex-col min-h-[100dvh] dark:bg-black dark:text-white">
      <header class="px-4 lg:px-6 h-14 flex items-center">
        <a class="flex items-center justify-center space-x-3" href="#">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-10 text-purple-700 font-bold"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" /></svg>
          <span class="font-bold text-xl">WareHouse</span>
        </a>
        <nav class="ml-auto flex items-center space-x-7">
          <a class="text-sm font-medium hover:underline underline-offset-4" href="#features">
            Features
        </a>
        <?php if(auth()->guard()->check()): ?>
        <a
                  class="inline-flex h-7 items-center justify-center rounded-md bg-gray-900 px-8 text-sm font-medium text-gray-50 shadow transition-colors hover:bg-gray-900/90 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-gray-950 disabled:pointer-events-none disabled:opacity-50 dark:bg-gray-50 dark:text-gray-900 dark:hover:bg-gray-50/90 dark:focus-visible:ring-gray-300"
                  href="/dashboard"
                >
                  Dashboard
        </a>
        <?php else: ?>
        <a
                  class="inline-flex h-7 items-center justify-center rounded-md bg-gray-900 px-8 text-sm font-medium text-gray-50 shadow transition-colors hover:bg-gray-900/90 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-gray-950 disabled:pointer-events-none disabled:opacity-50 dark:bg-gray-50 dark:text-gray-900 dark:hover:bg-gray-50/90 dark:focus-visible:ring-gray-300"
                  href="<?php echo e(route('register')); ?>"
                >
                  Get Started
        </a>
        <?php endif; ?>
        </nav>
      </header>
      <main class="flex-1">
        <section class="w-full py-12 md:py-24 lg:py-32">
          <div class="container px-4 md:px-6">
            <div class="flex flex-col items-center space-y-4 text-center">
              <div class="space-y-2">
                <h1 class="text-3xl font-bold tracking-tighter sm:text-4xl md:text-5xl">
                  Efficient Inventory Management Simplified
                </h1>
                <p class="mx-auto max-w-[700px] text-gray-500 md:text-xl/relaxed lg:text-base/relaxed xl:text-xl/relaxed dark:text-gray-400">
                  Increase your sales and keep track of every unit with our powerful stock management, order fulfillment, and inventory control software
                </p>
              </div>
              <div class="flex flex-col gap-2 min-[400px]:flex-row">
                <a
                  class="inline-flex h-10 items-center justify-center rounded-md bg-gray-900 px-8 text-sm font-medium text-gray-50 shadow transition-colors hover:bg-gray-900/90 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-gray-950 disabled:pointer-events-none disabled:opacity-50 dark:bg-gray-50 dark:text-gray-900 dark:hover:bg-gray-50/90 dark:focus-visible:ring-gray-300"
                  href="<?php echo e(route('register')); ?>"
                >
                  Get Started
        </a>
                <a
                  class="inline-flex h-10 items-center justify-center rounded-md border border-gray-200 border-gray-200 bg-white px-8 text-sm font-medium shadow-sm transition-colors hover:bg-gray-100 hover:text-gray-900 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-gray-950 disabled:pointer-events-none disabled:opacity-50 dark:border-gray-800 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300"
                  href="#features"
                >
                  Overview
        </a>
              </div>
            </div>
          </div>
        </section>
        <section class="w-full py-12 md:py-24 lg:py-32">
          <div class="container px-4 md:px-6">
            <div class="flex flex-col items-center justify-center space-y-4 text-center">
              <div class="space-y-2">
                <div class="inline-block rounded-lg bg-gray-100 px-3 py-1 text-sm dark:bg-black">
                  New Features
                </div>
                <h2 class="text-3xl font-bold tracking-tighter sm:text-5xl">Faster iteration. More innovation.</h2>
                <p class="max-w-[900px] text-gray-500 md:text-xl/relaxed lg:text-base/relaxed xl:text-xl/relaxed dark:text-gray-400">
                  Revolutionzing Inventory Management through Agile Iteration and Cutting-edge Innovations.
                </p>
              </div>
            </div>
            <div class="mx-auto grid max-w-5xl items-center gap-6 py-12 lg:grid-cols-2 lg:gap-12">
              <div class="relative">
              <img
                alt="Image"
                class="mx-auto aspect-video overflow-hidden rounded-xl object-cover object-center sm:w-full lg:order-last"
                height="310"
                src="/pc.jpeg"
                width="550"
              />
              <img
                alt="Image"
                class="absolute bottom-0 right-0 h-[80%] rounded-xl"
                src="/mobile.jpeg"
              />
              </div>

              <div class="flex flex-col justify-center space-y-4" id="features">
                <ul class="grid gap-6">
                  <li>
                    <div class="grid gap-1">
                      <h3 class="text-xl font-bold">Manage orders</h3>
                      <p class="text-gray-500 dark:text-gray-400">
                        Manage your offline and online orders with our efficient order management system. Manage purchase and sales orders all in the comfort of our user-freindly Inventory Management System.
                      </p>
                    </div>
                  </li>
                  <li>
                    <div class="grid gap-1">
                      <h3 class="text-xl font-bold">End-to-End Tracking</h3>
                      <p class="text-gray-500 dark:text-gray-400">
                        Enhancing Operational Efficiency with Comprehensive End to End Tracking Solutions.
                      </p>
                    </div>
                  </li>
                  <li>
                    <div class="grid gap-1">
                      <h3 class="text-xl font-bold">Insightful Visualisation</h3>
                      <p class="text-gray-500 dark:text-gray-400">
                        Harnessing Data to Drive Informed Decision-Making and Business Growth
                      </p>
                    </div>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </section>
        <section class="w-full py-12 md:py-24 lg:py-32 border-t">
          <div class="container grid items-center gap-6 px-4 md:px-6 lg:grid-cols-2 lg:gap-10">
            <div class="space-y-2">
              <h2 class="text-3xl font-bold tracking-tighter md:text-4xl/tight">
                Experience the workflow to optimize your business operations.
              </h2>
              <p class="max-w-[600px] text-gray-500 md:text-xl/relaxed lg:text-base/relaxed xl:text-xl/relaxed dark:text-gray-400">
                Optimize your business operations through automation and data-driven insights.
              </p>
            </div>
            <div class="flex flex-col gap-2 min-[400px]:flex-row lg:justify-end">
              <a
                class="inline-flex h-10 items-center justify-center rounded-md bg-gray-900 px-8 text-sm font-medium text-gray-50 shadow transition-colors hover:bg-gray-900/90 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-gray-950 disabled:pointer-events-none disabled:opacity-50 dark:bg-gray-50 dark:text-gray-900 dark:hover:bg-gray-50/90 dark:focus-visible:ring-gray-300"
                href="<?php echo e(route('register')); ?>"
              >
                Get Started
        </a>
              <a
                class="inline-flex h-10 items-center justify-center rounded-md border border-gray-200 border-gray-200 bg-white px-8 text-sm font-medium shadow-sm transition-colors hover:bg-gray-100 hover:text-gray-900 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-gray-950 disabled:pointer-events-none disabled:opacity-50 dark:border-gray-800 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300"
                href="#features"
              >
                Learn more
        </a>
            </div>
          </div>
        </section>
        <section class="w-full py-12 md:py-24 lg:py-32">
          <div class="container grid items-center gap-6 px-4 md:px-6 lg:grid-cols-2">
            <div class="space-y-4">
              <div class="inline-block rounded-lg bg-gray-100 px-3 py-1 text-sm dark:bg-black">Performance</div>
              <h2 class="lg:leading-tighter text-3xl font-bold tracking-tighter sm:text-4xl md:text-5xl xl:text-[3.4rem] 2xl:text-[3.75rem]">
                Maximizing Efficiency
              </h2>
              <a
                class="inline-flex h-9 items-center justify-center rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-gray-50 shadow transition-colors hover:bg-gray-900/90 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-gray-950 disabled:pointer-events-none disabled:opacity-50 dark:bg-gray-50 dark:text-gray-900 dark:hover:bg-gray-50/90 dark:focus-visible:ring-gray-300"
                href="<?php echo e(route('register')); ?>"
              >
                Get Started
        </a>
            </div>
            <div class="flex flex-col items-start space-y-4">
              <div class="inline-block rounded-lg bg-gray-100 px-3 py-1 text-sm dark:bg-black">Scale</div>
              <p class="mx-auto max-w-[700px] text-gray-500 md:text-xl/relaxed dark:text-gray-400">
                Elevating Perfomance through Optimized Processes and Enhanced Resource Utilization.
              </p>
              <a
                class="inline-flex h-9 items-center justify-center rounded-md border border-gray-200 border-gray-200 bg-white px-4 py-2 text-sm font-medium shadow-sm transition-colors hover:bg-gray-100 hover:text-gray-900 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-gray-950 disabled:pointer-events-none disabled:opacity-50 dark:border-gray-800 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300"
                href="<?php echo e(route('register')); ?>"
              >
                Get Started
        </a>
            </div>
          </div>
        </section>
      </main>
    </div>
    </body>
</html>
<?php /**PATH /home/suwilanji/work/supply-catena/resources/views/landing-page.blade.php ENDPATH**/ ?>