<style>
    @page {
        size: A4 portrait;
        margin: 0;
    }

    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        padding: 0;
        font-family: "DejaVu Sans", Arial, Helvetica, sans-serif;
        font-size: 10px;
        color: #1c1917;
        background: #ffffff;
    }

    .document {
        width: 100%;
        position: relative;
    }

    .header {
        width: 100%;
        height: 125px;
        position: relative;
        overflow: hidden;
        margin: 0;
        padding: 0;
    }

    .logo {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 118px;
        display: block;
        object-fit: fill;
    }

    .company {
        position: absolute;
        right: 25mm;
        top: 22px;
        width: 290px;
        text-align: right;
        line-height: 1.45;
        z-index: 2;
    }

    .company-name {
        font-size: 11px;
        font-weight: bold;
        color: #7a5234;
    }

    .company-text {
        font-size: 8.5px;
        color: #a08b78;
    }

    .title {
        text-align: center;
        margin-left: 15mm;
        margin-right: 15mm;
        margin-top: 5px;
        margin-bottom: 20px;
    }

    .title-main {
        font-size: 15px;
        font-weight: bold;
        margin-bottom: 2px;
    }

    .title-sub {
        font-size: 12px;
        color: #57534e;
    }

    table.form-table {
        width: calc(100% - 30mm);
        margin-left: 15mm;
        margin-right: 15mm;
        border-collapse: collapse;
        table-layout: fixed;
    }

    table.form-table td {
        border: 1px solid #777;
        vertical-align: top;
    }

    .info-cell {
        width: 50%;
        height: auto;
        padding: 5px 9px;
        font-size: 10px;
        line-height: 1.4;
    }

    .info-label {
        font-weight: bold;
    }

    .activity-cell {
        height: auto;
        min-height: 50px;
        padding: 5px 9px;
        vertical-align: top;
    }

    .activity-content {
        margin-top: 3px;
        line-height: 1.55;
    }

    .foto-section {
        width: calc(100% - 30mm);
        margin-left: 15mm;
        margin-right: 15mm;
        margin-top: 10px;
    }

    .foto-section .foto-label {
        font-weight: bold;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #7a5234;
        margin-bottom: 16px;
        page-break-after: avoid;
    }

    .foto-grid {
        font-size: 0;
        line-height: 0;
        margin-top: 4px;
    }

    .foto-item {
        display: inline-block;
        width: 320px;
        text-align: center;
        vertical-align: top;
        font-size: 10px;
        line-height: 1.3;
        margin-right: 16px;
        margin-bottom: 12px;
        page-break-inside: avoid;
    }

    .foto-cap {
        font-size: 8px;
        color: #7a5234;
        margin-bottom: 2px;
    }

    .foto-grid img {
        max-width: 310px;
        max-height: 250px;
        width: auto;
        height: auto;
        object-fit: contain;
        vertical-align: top;
        border: 1px solid #f0ece8;
        border-radius: 4px;
    }

    .approval {
        width: calc(100% - 30mm);
        margin-left: 15mm;
        margin-right: 15mm;
        margin-top: 10px;
    }

    .approval td {
        width: 33.333%;
        border: 1px dotted #777;
        text-align: center;
    }

    .approval-header {
        height: 38px;
        vertical-align: middle !important;
        font-size: 10px;
    }

    .approval-body {
        height: 190px;
        vertical-align: bottom !important;
        padding-bottom: 7px;
    }

    .signature-space {
        height: 125px;
        position: relative;
    }

    .stamp {
        position: absolute;
        width: 80px;
        height: 80px;
        object-fit: contain;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        opacity: 0.85;
    }

    .signature-name {
        font-size: 10px;
        text-decoration: underline;
    }

    .signature-role {
        margin-top: 2px;
        font-size: 10px;
    }
</style>
