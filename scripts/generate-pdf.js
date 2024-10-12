import puppeteer from 'puppeteer';
import path from 'path';
import { fileURLToPath } from 'url';

// Get the current file path
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Get tenant_id, document_type, and document_id from command-line arguments
const tenant_id = process.argv[2];
const document_type = process.argv[3];
const document_id = process.argv[4];
const data_id = process.argv[4];

(async () => {
  const browser = await puppeteer.launch();
  const page = await browser.newPage();

  // Example URL - change this to your actual document generation URL
  const url = `http://localhost:8000/documents/${document_type}/${tenant_id}/${document_id}/${data_id}`;

  try {

    await page.setViewport({
      width: 2000,  // Screen width (adjust as needed)
      height: 900,  // Screen height (adjust as needed)
      isMobile: false // Optionally set this for mobile-specific layouts
    });
    // Increase timeout to 60 seconds
    await page.goto(url, { waitUntil: 'networkidle0', timeout: 60000 }).catch(e => console.log(e));

    // Define the path where the PDF will be saved
    const pdfPath = path.join(__dirname, '../storage/app/public/generated-pdf.pdf');

    // Generate the PDF
    await page.pdf({
      path: pdfPath,
      format: 'A4',
    });

    console.log('PDF generated successfully.');
  } catch (error) {
    console.error('Failed to generate PDF:', error);
  } finally {
    await browser.close();
  }

  return true;
})();
