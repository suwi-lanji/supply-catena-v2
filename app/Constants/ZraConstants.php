<?php

namespace App\Constants;

class ZraConstants
{
    const PACKAGE_UNITS = [
        'AM' => 'Ampoule',
        'BA' => 'Barrel',
        'BC' => 'Bottle crate',
        'BE' => 'Bundle',
        'BF' => 'Balloon, non-protected',
        'BG' => 'Bag',
        'BJ' => 'Bucket',
        'BK' => 'Basket',
        'BL' => 'Bale',
        'BQ' => 'Bottle, protected cylindrical',
        'BR' => 'Bar',
        'BV' => 'Bottle, bulbous',
        'BZ' => 'Bag',
        'CA' => 'Can',
        'CH' => 'Chest',
        'CJ' => 'Coffin',
        'CL' => 'Coil',
        'CR' => 'Wooden Box, Wooden Case',
        'CS' => 'Cassette',
        'CT' => 'Carton',
        'CTN' => 'Container',
        'CY' => 'Cylinder',
        'DR' => 'Drum',
        'GT' => 'Extra Countable Item',
        'HH' => 'Hand Baggage',
        'IZ' => 'Ingots',
        'JR' => 'Jar',
        'JU' => 'Jug',
        'JY' => 'Jerry CAN Cylindrical',
        'KZ' => 'Canester',
        'LZ' => 'Logs, in bundle/bunch/truss',
        'NT' => 'Non-Exterior Packaging Unit',
        'OU' => 'Poddon',
        'PD' => 'Plate',
        'PG' => 'Pipe',
        'PO' => 'Pilot',
        'PU' => 'Traypack',
        'RL' => 'Reel',
        'RO' => 'Roll',
        'RZ' => 'Rods, in bundle/bunch/truss',
        'SK' => 'Skeletoncase',
        'TY' => 'Tank, cylindrical',
        'VG' => 'Bulk, gas (at 1031 mbar 15°C)',
        'VL' => 'Bulk, liquid (at normal temperature/pressure)',
        'VO' => 'Bulk, solid, large particles (nodules)',
        'VQ' => 'Bulk, gas (liquefied at abnormal temperature/pressure)',
        'VR' => 'Bulk, solid, granular particles (grains)',
        'VT' => 'Extra Bulk Item',
        'VY' => 'Bulk, fine particles (powder)',
        'ML' => 'Mills cigarette',
        'TN' => 'TAN1 TAN REFER TO 20 BAGS',
        'B/L' => 'Black Lug',
        'BIN' => 'Bin',
        'BOTT' => 'Bottle',
        'BOUQ' => 'Bouquet',
        'BOWL' => 'Bowl',
        'BOX' => 'Box',
        'BUBG' => 'Budget Bag',
        'BULK' => 'Bulk Pack',
        'BUNC' => 'Bunch',
        'BUND' => 'Bundle',
        'CLEA' => 'Clear Lid',
        'EA' => 'Each',
        'EACH' => 'Each',
        'ECON' => 'Economy Bag',
        'ECPO' => 'Econo Poc Sell',
        'G/L' => 'Green Lugs',
        'KARR' => 'Karripoc',
        'MESH' => 'Mesh',
        'NETL' => 'Netlon',
        'LABE' => 'Labels',
        'P/KG' => 'Per Kilogram',
        'PACK' => 'Pack',
        'PCRT' => 'PCRT',
        'PILP' => 'Pilpac',
        'POC' => 'Pocket',
        'POLY' => 'Poly Bags',
        'POT' => 'Pots',
        'POCS' => 'Prepack',
        'PREP' => 'Pun DTray',
        'PUND' => 'Punnet - Packaging',
        'PUNN' => 'Sleeve',
        'SLEE' => 'Sock',
        'SOCK' => 'Tray',
        'TRAY' => 'Tray Sell',
        'TRSE' => 'Tuba',
        'TUB' => 'Tuba',
        'UNWR' => 'Unwrap',
        'WRAP' => 'Wrapped',
    ];

    const CURRENCIES = [
        'ZMW' => 'Zambian Kwacha',
        'USD' => 'United States Dollar',
        'ZAR' => 'South African Rand',
        'GBP' => 'Pound Sterling',
        'CNY' => 'Chinese Yuan',
        'EUR' => 'Euro',
    ];

    const VAT_TYPES = [
        'A' => 'Standard Rated',
        'B' => 'Minimum Taxable Value (MTV)',
        'C1' => 'Exports',
        'C2' => 'Zero-rating Local Purchases',
        'C3' => 'Zero-rated by nature',
        'D' => 'Exempt',
        'E' => 'Disbursement',
        'RVAT' => 'Reverse VAT',
    ];

    const IPL_TYPES = [
        'IPL1' => 'Insurance Premium Levy',
        'IPL2' => 'Re-Insurance',
    ];

    const TL_TYPES = [
        'TL' => 'Tourism Levy',
        'F' => 'Service Charge',
    ];

    const EXCISE_TYPES = [
        'ECM' => 'Excise on Coal',
        'EXE' => 'Excise Electricity',
    ];

    const TT_TYPES = [
        'TOT' => 'Turnover Tax',
    ];

    const UNITS_OF_MEASURE = [
        '4B' => 'Pair',
        'AV' => 'Cap',
        'BA' => 'Barrel',
        'BE' => 'Bundle',
        'BG' => 'Bag',
        'BL' => 'Block',
        'BLL' => 'Barrel (petroleum) (158.987 dm3)',
        'BX' => 'Box',
        'CA' => 'Can',
        'CEL' => 'Cell',
        'CMT' => 'Centimetre',
        'CR' => 'Carat',
        'DR' => 'Drum',
        'DZ' => 'Dozen',
        'GLL' => 'Gallon',
        'GRM' => 'Gram',
        'GRO' => 'Gross',
        'KG' => 'Kilo-Gramme',
        'KTM' => 'Kilometre',
        'KWT' => 'Kilowatt',
        'L' => 'Litre',
        'LBR' => 'Pound',
        'LK' => 'Link',
        'LTR' => 'Litre',
        'M' => 'Metre',
        'M2' => 'Square Metre',
        'M3' => 'Cubic Metre',
        'MGM' => 'Milligram',
        'MTR' => 'Metre',
        'MWT' => 'Megawatt Hour (1000 kW.h)',
        'NO' => 'Number',
        'NX' => 'Part per Thousand',
        'PA' => 'Packet',
        'PG' => 'Plate',
        'PR' => 'Pair',
        'RL' => 'Reel',
        'RO' => 'Roll',
        'SET' => 'Set',
        'ST' => 'Sheet',
        'TNE' => 'Tonne (Metric Ton)',
        'TU' => 'Tube',
        'U' => 'Pieces/Item [Number]',
        'YRD' => 'Yard',
        'P1' => 'Pack',
        'PL' => 'Pallet',
        'Ft' => 'Feet',
        'MM' => 'Millimetre',
        'In' => 'Inches',
        'Oz' => 'Ounce',
        'YR' => 'Year',
        'M' => 'Month',
        'Wk' => 'Week',
        'D' => 'Day',
        'Hr' => 'Hour',
        'Ha' => 'Hectare',
        'yd2' => 'Square Yards',
        'ft2' => 'Square Feet',
        'cm2' => 'Square Centimetre',
        'm2' => 'Square Metre',
        'Pt' => 'Pints',
        'Qt' => 'Quarts',
        'MM' => 'Millilitre',
        'M/M' => 'Meter/Minute',
        'ML' => 'Microliter',
        '4O' => 'Microfarad',
        '4T' => 'Pikofarad',
        'A' => 'Ampere',
        'A87' => 'Gigaohm',
        'A93' => 'Gram/Cubic Meter',
        'ACR' => 'Acre',
        'B34' => 'Kilogram/Cubic Decimeter',
        'B45' => 'Kilomol',
        'B47' => 'Kilonewton',
        'B73' => 'Meganewton',
        'B75' => 'Megohm',
        'B78' => 'Megavolt',
        'B84' => 'Microampere',
        'BAG' => 'Bag',
        'BAR' => 'Bar',
        'BOT' => 'Bottle',
        'BQK' => 'Becquerel/Kilogram',
        'C10' => 'Millifarad',
        'C36' => 'Mol per Cubic Meter',
        'C38' => 'Mol per Liter',
        'C39' => 'Nanoampere',
        'C3S' => 'Cubic Centimeter/Second',
        'C41' => 'Nanofarad',
        'C56' => 'Newton/Square Millimeter',
        'CCM' => 'Cubic Centimeter',
        'CD' => 'Candela',
        'CDM' => 'Cubic Decimeter',
        'EA' => 'Each',
    ];

    const TRANSACTION_TYPE = [
        'C' => 'Copy',
        'N' => 'Normal',
    ];

    const SALES_RECEIPT_TYPE = [
        'S' => 'Sale',
        'R' => 'Refund after Sale',
        'D' => 'Adjustment upwards after Sale',
    ];

    const PAYMENT_METHODS = [
        '01' => 'CASH',
        '02' => 'CREDIT',
        '03' => 'CASH/CREDIT',
        '04' => 'BANK CHECK',
        '05' => 'DEBIT&CREDIT CARD',
        '06' => 'MOBILE MONEY',
        '07' => 'OTHER',
        '08' => 'Bank transfer',
    ];

    const TRANSACTION_PROGRESS = [
        '02' => 'Approved',
        '05' => 'Refunded',
        '06' => 'Transferred',
        '04' => 'Rejected',
    ];

    const REGISTRATION_TYPE = [
        'A' => 'Automatic',
        'M' => 'Manual',
    ];

    const PURCHASE_RECEIPT_TYPES = [
        'P' => 'Purchase',
        'R' => 'Refund after Purchase',
    ];

    const STOCK_IN_OUT_TYPE = [
        '01' => 'Incoming-Import',
        '02' => 'Incoming-Purchase',
        '03' => 'Incoming-Return',
        '04' => 'Incoming-Stock Movement',
        '05' => 'Incoming-Processing',
        '06' => 'Incoming-Adjustment',
        '11' => 'Outgoing-Sale',
        '12' => 'Outgoing-Return',
        '13' => 'Outgoing-Stock Movement',
        '14' => 'Outgoing-Processing',
        '15' => 'Outgoing-Discarding',
        '16' => 'Outgoing-Adjustment',
    ];

    // Credit Note Reason Code Constants
    const CREDIT_NOTE_REASON_CODE = [
        '011' => 'Wrong product(s)',
        '022' => 'Wrong price',
        '033' => 'Damaged Goods',
        '044' => 'Wrong Customer Invoiced',
        '055' => 'Duplicated invoice',
        '066' => 'Excess supplies',
        '077' => 'Other (Provide other reason in brief)',
    ];

    const IMPORT_ITEM_STATUS = [
        '3' => 'Approved - These are imported items approved to be stocked-in',
        '4' => 'Rejected - These are non-stock imports',
    ];

    const DEBIT_NOTE_REASON_CODE = [
        '01' => 'Wrong quantity invoiced',
        '02' => 'Wrong invoice amount',
        '03' => 'Omitted item',
        '04' => 'Other [specify]',
    ];
}
