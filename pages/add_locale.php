<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-06-08 07:03:18
 * @modify date 2023-06-09 08:19:20
 * @license GPLv3
 * @desc [description]
 */

use Gettext\Scanner\PhpScanner;
use Gettext\Generator\PoGenerator;
use Gettext\Translations;
use Symfony\Component\Finder\Finder;
use SLiMS\Filesystems\Storage;

defined('INDEX_AUTH') or die('Direct access is not allowed!');

require SIMBIO . 'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO . 'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';


if (isset($_POST['create'])) {
    toastr(__('Data on progress, please wait'))->info();
    ob_flush();
    flush();
    $phpScanner = new PhpScanner(Translations::create($_POST['code']));
    $phpScanner->setDefaultDomain($_POST['code']);

    $iterator = Finder::create()
        ->files()
        ->name('*.php')
        ->exclude(SB . 'plugins/')
        ->in(SB);
    
    // Create folder
    Storage::plugin()->makeDirectory($targetDirectory = POLYGLOT_BASE . '/resources/lang/' . $_POST['code'] . '/LC_MESSAGES/');

    $phpScanner->ignoreInvalidFunctions();
    
    foreach ($iterator as $file) {
        $phpScanner->scanFile($file->getPathname());
    }

    toastr(__('Generate data to PO'))->info();
    ob_flush();
    flush();
    //Save the translations in .po files
    $generator = new PoGenerator();
    
    $headers = [
        'Project-Id-Version' => SENAYAN_VERSION . ' - ' . SENAYAN_VERSION_TAG,
        'Last-Translator' => $_POST['translator'],
        'Language' => $_POST['code'],
        'POT-Creation-Date' => date('Y-m-d H:i:sP'),
        'PO-Revision-Date' => date('Y-m-d H:i:sP'),
        'MIME-Version' => '1.0',
        'Content-Type' => 'text/plain; charset=UTF-8',
        'Content-Transfer-Encoding' => '8bit',
        'Plural-Forms' => 'nplurals=2; plural=n != 1;'
    ];

    foreach ($phpScanner->getTranslations() as $domain => $translations) {
        foreach ($headers as $header => $value) {
            $translations->getHeaders()->set($header, $value);
        }
        $generator->generateFile($translations, SB . 'plugins/' . $targetDirectory . "messages.po");
    }

    toastr(__('Data has been generated'))->success();
    
    \SLiMS\Jquery::raw('colorbox.close()');
    redirect()->simbioAJAX(url(reset: true));
    exit;
}

ob_start();
$form = new simbio_form_table_AJAX('mainForm', url(['page' => 'add_locale']), 'post');
$form->submit_button_attr = 'name="create" value="' . __('Create') . '" class="s-btn btn btn-default"';
// form table attributes
$form->table_attr = 'id="dataList" cellpadding="0" cellspacing="0"';
$form->table_header_attr = 'class="alterCell"';
$form->table_content_attr = 'class="alterCell2"';

$codes = array_map(function($lang){
    $label = explode(' - ', $lang[1]);
    unset($label[array_key_last($label)]);
    $lang[1] = implode(' - ', $label);
    return $lang;
}, currency('')->getIsoCode());
sort($codes);


$form->addTextField('text', 'translator', __('Translator'), '', 'rows="1" class="form-control"', __('People name who create locale'));
$form->addTextField('text', 'email', __('E-Mail'), '', 'rows="1" class="form-control"', '');
$form->addSelectList('code', __('Region'), $codes, '', 'class="select2"', __('Code'));

echo $form->printOut();
echo <<<HTML
<script>
    // select 2
    $('.select2').each( function(idx) {
      var selectObj = $(this);
      var ajaxHandler = selectObj.attr('data-src')
      if (ajaxHandler) {
        var dataSourceTable = selectObj.attr('data-src-table');
        var dataSourceCols = selectObj.attr('data-src-cols');
        selectObj.ajaxChosen({
          jsonTermKey: 'keywords',
          type: 'POST',
          url: ajaxHandler,
          // data: 'tableName='+dataSourceTable+'&tableFields='+dataSourceCols,
          data: {tableName:dataSourceTable, tableFields:dataSourceCols},
          dataType: 'json', contentType: 'application/json' },
          function (data) {
            var results = [];
            $.each(data, function (i, val) {
              results.push({ value: val.id, text: val.text });
            });
            return results;
          });
      } else {
        selectObj.chosen();
      }
    });
</script>
HTML;
$content = ob_get_clean();
require SB . '/admin/' . $sysconf['admin_template']['dir'] . '/notemplate_page_tpl.php';
exit;