<?
/**
 * Template variables
 * @var $flight_day
 * @var $planes
 * @var $is_manual
 * @var $plane_selection
 */
if (empty($att))
{
    // Standard values
    $att = new Attendance([]);
    $att->user_id = $_SESSION["user_id"];
    $att->is_planned = false;
}
$today = new DateTime();
$today->setTime(0, 0, 0, 0);
$flight_day->setTime(0, 0, 0, 0);
?>
<form id="attend_form" action="/src/Controller/AttendanceController.php" method="POST" x-data="attendance">
    <input type="hidden" name="action" value="<?= empty($att->id) ? 'insert' : 'update' ?>"/>
    <? if (!empty($att->id)) echo '<input type="hidden" name="id" value="' . $att->id . '">' ?>
    <input type="hidden" name="flight_day" value="<?= $flight_day->format('Y-m-d') ?>"/>
    <input type="hidden" name="user_id" value="<?= empty($att->id) ? $_SESSION["user_id"] : $att->user_id ?>"/>
    <input type="hidden" name="plane_selection" x-model="plane_selection"/>
    <input type="hidden" name="pos_latitude" x-model="pos_latitude"/>
    <input type="hidden" name="pos_longitude" x-model="pos_longitude"/>
    <input type="hidden" name="is_planned"
        <?= empty($att->id) && $today == $flight_day && !$is_manual ? 'x-model="distance_invalid"' : '' ?>
        value="<?= ($att->is_planned || ($today < $flight_day)) ?>"/>
    <div class="row g-3">
        <? if (!empty($planes)) { ?>
            <label><?= empty($att->id) ? 'Was möchtest du fliegen?' : 'Flugzeugauswahl:' ?></label>
            <div class="form-group col-md-7">
                <div class="list-group">
                    <?
                    foreach($planes as $plane)
                    {
                        ?>
                        <label class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <input class="form-check-input me-1 plane_btn"
                                        type="checkbox"
                                        <? if (in_array($plane->id, $plane_selection)) echo 'checked' ?>
                                        data-plane_id="<?= $plane->id ?>"
                                        x-data="plane_selection_option(<?= $plane->id ?>)"
                                        x-model="is_selected">
                                <?= $plane->wkz . "; <strong>" . $plane->model . "</strong> (" . $plane->lfz . ")" ?>
                            </div>
                            <?
                                if (!empty($list_by_plane[$plane->id]))
                                {
                                    ?><span class="badge bg-primary rounded-pill"><?= count($list_by_plane[$plane->id]["attendance_list"]) ?></span><?
                                }
                            ?>
                        </label>
                        <?
                    }
                    ?>
                </div>
                <small class="form-text text-muted">optional</small>
            </div>
        <? } ?>
        <div class="col">
            <div class="row g-3">
                <? if ($is_manual) { ?>
                    <div class="form-group col-12">
                        <label for="manual_entry_name_field">Name:</label>
                        <input id="manual_entry_name_field" name="manual_entry" type="text" class="form-control" required placeholder="Der Name der Person die du eintragen möchtest" value="<?= $att->manual_entry ?>">
                        <small class="form-text text-muted">Um jemand anderes manuell in die Liste einzutragen (optional).</small>
                    </div>
                <? } ?>
                <div class="form-group col-12">
                    <label for="role_field"><?= empty($att->id) ? 'Als' : 'Rolle' ?>:</label>
                    <select id="role_field" name="role" class="form-select">
                        <?
                        foreach(ATTENDANCE_ROLES as $key => $role)
                        {
                            ?><option value="<?= $key ?>" <?= $att->role == $key ? 'selected' : '' ?>><?= $role["name"] ?></option><?
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-12 <?= !empty($att->id) && empty($att->is_planned) && $today == $flight_day ? 'd-none' : '' ?>">
                    <label for="eta_time_field">Wann etwa <?= $is_manual ? 'wird er/sie' : 'wirst du' ?> da sein?</label>
                    <div class="input-group" x-data="{ eta_time: '<?= isset($att->time) ? $att->time->format('H:i:s') : $_REQUEST['time'] ?? '' ?>' }">
                        <span class="input-group-text" id="eta-lbl">ETA</span>
                        <input id="eta_time_field" name="time" type="time" class="form-control" x-model="eta_time" placeholder="ETA" aria-label="ETA" aria-describedby="eta-lbl">
                        <button type="button" class="btn btn-danger input-group-text" @click="eta_time = null"><i class="fas fa-times"></i></button>
                    </div>
                    <small class="form-text text-muted">optional</small>
                </div>
                <div class="form-group col-12">
                    <div class="form-check">
                        <label class="form-check-label" x-data="checkbox_helper(<?= !empty($att->first) ?>)">
                            <input type="hidden" name="first" x-model="is_checked_number">
                            <input class="form-check-input hidden_input" type="checkbox" x-model="is_checked">
                            Letztes Mal nicht dran gewesen <i class="far fa-frown"></i>
                        </label>
                    </div>
                </div>
                <?
                if (empty($att->id) && $today == $flight_day && !$is_manual)
                {
                    ?>
                    <div class="col-12" :class="{ 'd-none': !distance_error }">
                        <div class="alert alert-danger m-0" role="alert" x-html="distance_error_message"></div>
                    </div>
                    <div class="col-12" :class="{ 'd-none': distance_error || !distance_valid }">
                        <div class="alert alert-success m-0" role="alert">
                            Du bist nah genug am Flugplatz um dich als <b>anwesend</b> einzutragen.
                            <div class="form-group pt-2">
                                <button type="submit" class="form-control btn btn-primary">Trag mich ein!</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-12" :class="{ 'd-none': distance_error || distance_valid }">
                        <div class="alert alert-warning m-0" role="alert">
                            <i class="fas fa-map-marker-alt"></i>
                            Du bist noch <strong class="distance" x-text="distance"></strong> zu weit vom Flugplatz entfernt um dich als <b>anwesend</b> einzutragen.
                            Du kannst dich aber unter "plant zu kommen" eintragen.
                            <div class="form-group pt-2">
                                <button type="submit" class="form-control btn btn-primary">werde da sein</button>
                            </div>
                        </div>
                    </div>
                    <?
                }
                else if (!empty($att->id))
                {
                    ?>
                    <div class="form-group col-xl-6 col-md-12 col-sm-6 col-12">
                        <button type="submit" class="form-control btn btn-primary">Änderungen übernehmen</button>
                    </div>
                    <div class="form-group col-xl-6 col-md-12 col-sm-6 col-12">
                        <a href="/src/Controller/AttendanceController.php?action=delete&attendance_id=<?= $att->id ?>" class="form-control btn btn-danger"><i class="far fa-trash-alt"></i> Eintrag löschen</a>
                    </div>
                    <?
                }
                else
                {
                    ?>
                    <div class="form-group col-12">
                        <button type="submit" class="form-control btn btn-primary"><?= $is_manual ? 'eintragen' : 'werde da sein' ?></button>
                    </div>
                    <?
                }
                ?>
            </div>
        </div>
    </div>
</form>