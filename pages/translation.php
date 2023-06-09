<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-06-07 13:48:29
 * @modify date 2023-06-09 15:01:56
 * @license GPLv3
 * @desc [description]
 */

use Symfony\Component\Finder\Finder;
use SLiMS\Json;
use SLiMS\Filesystems\Storage;
use Gettext\Loader\PoLoader;
use Gettext\Generator\MoGenerator;

defined('INDEX_AUTH') OR die('Direct access not allowed!');

require SB . 'admin/default/session.inc.php';
require SB . 'admin/default/session_check.inc.php';

$location = __DIR__ . DS . basename($_GET['page']??'') . '.php';
if (isset($_GET['page'])) {
    if (file_exists($location))
    {
        include $location;
        exit;
    }
    else
    {
        exit(<<<HTML
        <div class="alert alert-danger text-center">
            <strong>Page not found!</strong>
        </div>
        HTML);
    }
}

if (isset($_POST['publish']) && isset($_POST['langCode']))
{
    //import from a .po file:
    $loader = new PoLoader();
    $generator = new MoGenerator();
    $translations = $loader->loadFile($localePath = POLYGLOT_BASE_PATH . '/resources/lang/' . basename($_POST['langCode']) . '/LC_MESSAGES/messages.po');
    
    Storage::plugin()->makeDirectory($newDir = 'lang' . DS . basename($_POST['langCode']) . DS . 'LC_MESSAGES' . DS);
    $generator->generateFile($translations, SB . 'plugins' . DS . $newDir . DS . 'messages.mo');
    exit(Json::stringify(['status' => true, 'message' => __('Data has been saved!')])->withHeader());
}

?>
<div class="menuBox">
<div class="menuBoxInner memberIcon">
	<div class="per_title">
    	<h2>Translatation</h2>
    </div>
    <div class="sub_section">
	<div class="btn-group">
        <a href="<?= url(['page' => 'add_locale']) ?>" class="btn btn-primary openPopUp notAJAX" title="<?= __('Locale Form') ?>"><?= __('Create Locale'); ?></a>
	</div>
	</div>
</div>
</div>
<?php
if (!isset($_GET['form'])) {
    $finder = new Finder();
    $finder
        ->directories()
        ->depth('== 0')
        ->in(__DIR__ . '/../resources/lang');

    // existension check
    if ($finder->hasResults() === false) {
        $translate = "You don't have any custom language";
        exit(<<<HTML
        <div class="alert alert-secondary text-center">
            <span>{$translate}</span>
        </div>
        HTML);
    }

    // generate card
    echo '<div class="card-deck mx-3">';
    foreach ($finder as $directory) {
        $lastModified = \Carbon\Carbon::parse(date('Y-m-d H:i:s', $directory->getMTime()))->locale(config('default_lang'))->isoFormat('dddd, LL');
        $icon = substr($directory->getRelativePathname(), -2);
        $url = url(['page' => 'edit', 'lang' => $directory->getRelativePathname()]);
        echo <<<HTML
        <div class="card">
            <div class="card-body">
                <img src="https://www.countryflagicons.com/FLAT/64/{$icon}.png">
                <h5 class="card-title font-weight-bold">{$directory->getRelativePathname()}</h5>
                <p class="card-text">Translation of {$directory->getRelativePathname()}</p>
                <p class="card-text d-flex flex-row justify-content-between align-items-center">
                    <small class="text-muted">Last updated : {$lastModified}</small>
                    <div>
                        <a href="{$url}" title="Edit current locale for {$directory->getRelativePathname()}" width="1200" height="600" class="notAJAX openPopUp btn btn-sm btn-outline-secondary">Edit</a>
                        <button data-lang="{$directory->getRelativePathname()}" class="lang-publish btn btn-sm btn-outline-primary">Publish</button>
                    </div>
                </p>
            </div>
        </div>
        HTML;
    }
    echo '</div>';
}
?>
<script>
    $('.lang-publish').click(function(){
        let lang = $(this).data('lang')
        $.post('<?= url() ?>', {publish: true, langCode: lang}, function(response, state){
            if (response.status) {
                window.toastr.success(response.message, 'Yay')
            }
        })
    })
</script>