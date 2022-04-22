<?php
/**
 * Invoice Ninja (https://invoiceninja.com).
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2021. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://www.elastic.co/licensing/elastic-license
 */

namespace App\Export\CSV;

use App\Libraries\MultiDB;
use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Transformers\InvoiceTransformer;
use App\Utils\Ninja;
use Illuminate\Support\Facades\App;
use League\Csv\Writer;

class InvoiceItemExport
{
    private $company;

    private $report_keys;

    private $invoice_transformer;

    private array $entity_keys = [
        'amount' => 'amount',
        'balance' => 'balance',
        'client' => 'client_id',
        'custom_surcharge1' => 'custom_surcharge1',
        'custom_surcharge2' => 'custom_surcharge2',
        'custom_surcharge3' => 'custom_surcharge3',
        'custom_surcharge4' => 'custom_surcharge4',
        'custom_value1' => 'custom_value1',
        'custom_value2' => 'custom_value2',
        'custom_value3' => 'custom_value3',
        'custom_value4' => 'custom_value4',
        'date' => 'date',
        'discount' => 'discount',
        'due_date' => 'due_date',
        'exchange_rate' => 'exchange_rate',
        'footer' => 'footer',
        'number' => 'number',
        'paid_to_date' => 'paid_to_date',
        'partial' => 'partial',
        'partial_due_date' => 'partial_due_date',
        'po_number' => 'po_number',
        'private_notes' => 'private_notes',
        'public_notes' => 'public_notes',
        'status' => 'status_id',
        'tax_name1' => 'tax_name1',
        'tax_name2' => 'tax_name2',
        'tax_name3' => 'tax_name3',
        'tax_rate1' => 'tax_rate1',
        'tax_rate2' => 'tax_rate2',
        'tax_rate3' => 'tax_rate3',
        'terms' => 'terms',
        'total_taxes' => 'total_taxes',
        'currency' => 'currency_id',
        'qty' => 'item.quantity',
        'unit_cost' => 'item.cost',
        'product_key' => 'item.product_key',
        'cost' => 'item.product_cost',
        'notes' => 'item.notes',
        'discount' => 'item.discount',
        'is_amount_discount' => 'item.is_amount_discount',
        'tax_rate1' => 'item.tax_rate1',
        'tax_rate2' => 'item.tax_rate2',
        'tax_rate3' => 'item.tax_rate3',
        'tax_name1' => 'item.tax_name1',
        'tax_name2' => 'item.tax_name2',
        'tax_name3' => 'item.tax_name3',
        'line_total' => 'item.line_total',
        'gross_line_total' => 'item.gross_line_total',
        'invoice1' => 'item.custom_value1',
        'invoice2' => 'item.custom_value2',
        'invoice3' => 'item.custom_value3',
        'invoice4' => 'item.custom_value4',
    ];

    private array $decorate_keys = [
        'client',
        'currency',
    ];

    public function __construct(Company $company, array $report_keys)
    {
        $this->company = $company;
        $this->report_keys = $report_keys;
        $this->invoice_transformer = new InvoiceTransformer();
    }

    public function run()
    {

        MultiDB::setDb($this->company->db);
        App::forgetInstance('translator');
        App::setLocale($this->company->locale());
        $t = app('translator');
        $t->replace(Ninja::transformTranslations($this->company->settings));

        //load the CSV document from a string
        $this->csv = Writer::createFromString();

        //insert the header
        $this->csv->insertOne($this->buildHeader());

        Invoice::with('client')->where('company_id', $this->company->id)
                            ->where('is_deleted',0)
                            ->cursor()
                            ->each(function ($invoice){

                                $this->iterateItems($invoice);

                            });

        return $this->csv->toString(); 

    }

    private function buildHeader() :array
    {

        $header = [];

        foreach(array_keys($this->report_keys) as $key)
            $header[] = ctrans("texts.{$key}");

        return $header;
    }

    private function iterateItems(Invoice $invoice)
    {
        $transformed_invoice = $this->buildRow($invoice);

        $transformed_items = [];

        foreach($invoice->line_items as $item)
        {
            $item_array = [];

            foreach(array_values($this->report_keys) as $key){
            
                if(str_contains($key, "item.")){

                    $key = str_replace("item.", "", $key);
                    $item_array[$key] = $item->{$key};
                }

            }

            $entity = [];

            $transformed_items = array_merge($transformed_invoice, $item_array);

            $transformed_items = $this->decorateAdvancedFields($invoice, $transformed_items);

            foreach(array_values($this->report_keys) as $key)
            {
                $key = str_replace("item.", "", $key);
                $entity[$key] = $transformed_items[$key];
            }

            $this->csv->insertOne($entity); 

        }

    }

    private function buildRow(Invoice $invoice) :array
    {

        $transformed_invoice = $this->invoice_transformer->transform($invoice);

        $entity = [];

        foreach(array_values($this->report_keys) as $key){

            if(!str_contains($key, "item."))    
                $entity[$key] = $transformed_invoice[$key];

        }

        return $this->decorateAdvancedFields($invoice, $entity);

    }

    private function decorateAdvancedFields(Invoice $invoice, array $entity) :array
    {
        if(array_key_exists('currency_id', $entity))
            $entity['currency_id'] = $invoice->client->currency()->code;

        if(array_key_exists('client_id', $entity))
            $entity['client_id'] = $invoice->client->present()->name();

        if(array_key_exists('status_id', $entity))
            $entity['status_id'] = $invoice->stringStatus($invoice->status_id);

        return $entity;
    }

}