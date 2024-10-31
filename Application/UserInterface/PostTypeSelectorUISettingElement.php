<?php
namespace Slackr\Application\UserInterface;

if (!defined('ABSPATH'))
{
    exit;
}

class PostTypeSelectorUISettingElement extends AbstractUISettingElement
{
    /** @var  string[] */
    private $activePostTypes;

    /**
     * PostTypeSelectorUIElement constructor.
     * @param string $settingKey
     * @param string[] $activePostTypes
     */
    public function __construct($settingKey, $activePostTypes)
    {
        parent::__construct($settingKey);
        $this->activePostTypes = $activePostTypes;

    }

    /** @return string */
    public function getContent()
    {
        $postTypes = get_post_types([
            'public'   => true,
            '_builtin' => true
        ], 'objects');

        $activePostTypes = $this->activePostTypes;

        ob_start();
        ?>
        
        <?php
        $i = 0;
        foreach($postTypes as $postType)
        {
            ?>
            <label>
                <input type="hidden" name="<?=$this->settingKey?>[<?=$i?>][name]" value="<?=$postType->name?>" />
                <input type="checkbox" name="<?=$this->settingKey?>[<?=$i?>][isActive]" id="<?=$this->settingKey?>[<?=$i?>][isActive]" <?=checked(in_array($postType->name, $activePostTypes))?>>
                <?=$postType->label?> <br />
            </label>
            <?php
            $i++;
        }

        $content = ob_get_clean();

        return $content;
    }
}