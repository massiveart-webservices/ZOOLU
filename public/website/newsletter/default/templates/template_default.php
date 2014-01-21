<table cellpadding="0" cellspacing="0" border="0" id="Content">
    <?php if (!isset($this->isPreview) || $this->isPreview == false): ?>
    <tr>
        <td colspan="2"><?php echo $objHelper->getPreviewLink(); ?></td>
    </tr>
    <?php endif; ?>
    <tr>
        <td colspan="2"><?php echo $objHelper->getTitle('h1', false); ?></td>
    </tr>
    <tr>
        <td colspan="2"><?php echo $objHelper->getSalutation(); ?></td>
    </tr>
    <tr>
        <td colspan="2"><?php echo $objHelper->getArticle(); ?></td>
    </tr>
    <?php echo $objHelper->getTextBlocks('220x'); ?>
    <?php if (!isset($this->isPreview) || $this->isPreview == false): ?>
    <tr>
        <td><?php echo $objHelper->getUnsubscribeLink(); ?></td>
    </tr>
    <?php endif; ?>
</table>
