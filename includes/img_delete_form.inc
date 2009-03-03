<form id="img_delete_form" method="post" accept-charset="utf-8">
  <label for="delete">Delete</label>
  <select id="delete" name="delete">
    <option value="">-- Välj en bild att ta bort</option>
    <?php foreach ($images as $image): ?>
    <option value="<?php print $image['id']; ?>"><?php print htmlspecialchars($image['name']); ?></option>
    <?php endforeach; ?>
  </select><br/>
  <input type="submit" value="Delete &rarr;" />
</form>