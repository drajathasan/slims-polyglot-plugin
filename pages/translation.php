<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-06-07 13:48:29
 * @modify date 2023-06-08 14:40:03
 * @license GPLv3
 * @desc [description]
 */

use Symfony\Component\Finder\Finder;

defined('INDEX_AUTH') OR die('Direct access not allowed!');

require SB . 'admin/default/session.inc.php';
require SB . 'admin/default/session_check.inc.php';

$location = __DIR__ . DS . basename($_GET['page']??'') . '.php';
if (isset($_GET['page']) && file_exists($location)) {
    include $location;
    exit;
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
        echo <<<HTML
        <div class="card">
            <div class="card-body">
                <h5 class="card-title font-weight-bold">{$directory->getRelativePathname()}</h5>
                <p class="card-text">Translation of {$directory->getRelativePathname()}</p>
                <p class="card-text d-flex flex-row justify-content-between align-items-center">
                    <small class="text-muted">Last updated : {$lastModified}</small>
                    <div>
                        <button class="btn btn-sm btn-outline-secondary">Edit</button>
                        <button class="btn btn-sm btn-outline-primary">Publish</button>
                    </div>
                </p>
            </div>
        </div>
        HTML;
    }
    echo '</div>';
}
?>