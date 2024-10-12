// generate_pdf.js

const puppeteer = require('puppeteer');
const fs = require('fs');

// Get arguments from command line (HTML content, output path)
const args = process.argv.slice(2);
const htmlFilePath = args[0];
const outputPdfPath = args[1];

(async () => {
  const browser = await puppeteer.launch();
  const page = await browser.newPage();

  // Load the HTML content from a file
  const htmlContent = fs.readFileSync(htmlFilePath, 'utf-8');

  // Set the HTML content of the page
  await page.setContent(htmlContent, { waitUntil: 'domcontentloaded' });

  // Generate the PDF
  await page.pdf({
    path: outputPdfPath,
    format: 'A4', // You can change this to your preferred format
    printBackground: true, // Ensure background images are included
  });

  await browser.close();
})();
