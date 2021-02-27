<article class="container-fluid px-1 table-responsive">
    <table class="table table-sm caption-top attendance_table">
        <caption class="text-center"><?= $caption ?></caption>
        <thead class="border-bottom">
            <tr>
                <th scope="col">Zeit</th>
                <th scope="col">Name</th>
                <?= empty($plane_selection) ? '' : '<th scope="col">Flugzeuge</th>' ?>
            </tr>
        </thead>
        <tbody>
            <?
            if (empty($list))
            {
                ?>
                <tr>
                    <td colspan="3" class="text-center"><em>keine Einträge</em></td>
                </tr>
                <?
            }
            else
            {
                foreach ($list as $att)
                {
                    $is_user = $att->user_id == $_SESSION["user_id"];
                    $is_manual = !empty($att->manual_entry);
                    ?>
                    <tr data-id="<?= $att->id ?>"
                        data-time="<?= empty($att->time) ? '' : $att->time->format('H:i:s') ?>"
                        class="<?= (empty($att->manual_entry) ? '' : 'manual_entry ') ?>role_<?= $att->role ?> <?= ($is_user && !$is_manual) ? 'table-primary' : '' ?>"
                        <?= ($is_user && $is_manual) ? ' draggable="true"' : '' ?>>
                        <td><?= empty($att->time) || !empty($att->manual_entry) ? '' : $att->time->format("H:i") ?></td>
                        <td><?= $att->GetNameAndSymbols(); ?></td>
                        <?
                        if (!empty($plane_selection))
                        {
                            echo '<td>';
                            foreach ($plane_selection[$att->id] as $plane)
                            {
                                ?><span class="badge bg-primary rounded-pill"><?= $plane->alias ?></span> <?
                            }
                            echo '</td>';
                        }
                        ?>
                    </tr>
                    <?
                }
            }
            ?>
        </tbody>
    </table>
</article>