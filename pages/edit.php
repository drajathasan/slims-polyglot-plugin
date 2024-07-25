<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-06-09 09:40:38
 * @modify date 2024-07-25 22:20:04
 * @license GPLv3
 * @desc [description]
 */

use Gettext\Loader\PoLoader;
use Gettext\Generator\PoGenerator;

defined('INDEX_AUTH') or die('Direct access is not allowed!');

// import table instance
require SIMBIO . 'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO . 'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';

// set global variable
$loader = new PoLoader();
$targetPath = POLYGLOT_BASE_PATH . '/resources/lang/' . basename($_GET['lang']??$_POST['lang']??'') . '/LC_MESSAGES/messages.po';

if (isset($_GET['max_input_vars'])) {
    $input = (int)$_GET['max_input_vars'];
    $content  = 'php_value max_input_vars ' . $input .PHP_EOL;
    $content .= 'php_value post_max_size 16M'  . PHP_EOL;
    file_put_contents(SB . '.htaccess', $content, FILE_APPEND | LOCK_EX);
    sleep(2);
    redirect()->back();
}

// Update process
if (isset($_POST['update']))
{
    $generator = new PoGenerator();
    $translations = $loader->loadFile($targetPath);

    foreach ($_POST['trans'] as $original => $value) {
        if (empty($value)) continue;
        if ($translation = $translations->find(null, htmlspecialchars_decode($original))) {
            $translation->translate($value);
        }
    }

    $translations->getHeaders()->set('PO-Revision-Date', date('Y-m-d H:i:sP'));
    // Re-Create new po file
    $generator->generateFile($translations, $targetPath);

    // output
    toastr(__('Data has been updated'))->success();
    redirect()->simbioAJAX(url: url(), selector: '#pageContent', position: 'parent.');
}

ob_start();
$form = new simbio_form_table_AJAX('mainForm', url(['page' => 'edit']), 'post');
$form->submit_button_attr = 'name="update" value="' . __('Update') . '" class="s-btn btn btn-default"';
// form table attributes
$form->table_attr = 'id="dataList" cellpadding="0" cellspacing="0"';
$form->table_header_attr = 'class="alterCell"';
$form->table_content_attr = 'class="alterCell2"';

//import from a .po file:
try {
    $form->addHidden('lang', $_GET['lang']);
    
    $translations = $loader->loadFile(POLYGLOT_BASE_PATH . '/resources/lang/' . basename($_GET['lang']) . '/LC_MESSAGES/messages.po');
    
    // Mapping data for value check sort
    $translationItem = array_values(array_map(function($translation){
        return [$translation->getOriginal(), $translation->getTranslation()];
    }, $translations->getTranslations()));
    uasort($translationItem, fn($a) => $a[1] ? 1 : -1);

    // Iterate items for translational
    foreach ($translationItem as $item) {
        list($original, $translationText) = $item;

        // Register as text field
        $form->addTextField(
            // Text area for long text, text for short
            (strlen($original) > 50 ? 'textarea' : 'text'), 
            
            // set translatation name
            'trans['.htmlspecialchars($original).']', 
            
            // set label
            '<strong>' . htmlspecialchars($original) . '</strong>', 
            
            // set translatation text label
            htmlspecialchars($translationText), 

            // Other attribute
            'rows="1" '.(strlen($original) > 50 ? 'style="height: ' . strlen($original).'px"': '') . 
            ' placeholder="Translate on here" class="form-control border '.(empty($translationText) ? 'border-danger' : 'border-success').'"', '');
        ob_flush();
        flush();
    }

    
    $totalTranslation = count($translationItem);
    $show = ini_get('max_input_vars') < $totalTranslation;
    if (isset($_GET['action']) && $_GET['action'] === 'max_input_vars' && $show)
    {
        $currentHtaccess = file_get_contents($filePath = SB . '.htaccess');
        $currentHtaccess = $currentHtaccess . "\n" . 'php_value max_input_vars ' . (count($translationItem) + 1000);
        file_put_contents($filePath, $currentHtaccess);
        redirect()->simbioAJAX(url: url(), selector: '#pageContent', position: 'parent.');
    }
    
    // Alert
    if ($show)
    {
        echo '<div class="alert alert-warning">';
        echo '<h3>Warning</h3>';
        echo str_replace('{total_translate}', $totalTranslation, __('Max input vars in your system is less than {total_translate}. Make it greater than {total_translate}.'));
        echo '<a href="' . url(['max_input_vars' => $totalTranslation + 100]) . '" onclick="top.window.toastr.info(\'Please wait\', \'Info\')" class="ml-3 btn btn-primary">' . __('Increase Now') . '</a>';
        echo '</div>';
    }

    // Header info
    echo '<div class="alert alert-info">';
    echo '<h3>Translatation Info</h3>';
    foreach ($translations->getHeaders() as $header => $value) {
        echo '<div class="d-flex flex-row">
        <strong class="col-3">' . $header . '</strong> <strong class="mr-1">:</strong> <strong>' . $value . '</strong>
        </div>';
    }
    echo '</div>';

    echo <<<HTML
        <script>
            parent.$('#cboxLoadedContent').prepend(`
            <div class="w-100 bg-white p-3">
                <input type="text" class="form-control" placeholder="Search Some Text" style="position: absolute;bottom: 10%;"/>
            </div>
            `)
        </script>
    HTML;
    // End header info
    
    echo $form->printOut();
} catch (Exception $e) {
    echo <<<HTML
    <div class="alert alert-danger text-center">
        <strong>{$e->getMessage()}</strong>
    </div>
    HTML;
}

$content = ob_get_clean();
require SB . '/admin/' . $sysconf['admin_template']['dir'] . '/notemplate_page_tpl.php';
exit;