<?
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
<form id="attend_form" action="src/Controller/AttendanceController.php" method="POST">
    <input type="hidden" name="action" value="<?= empty($att->id) ? 'insert' : 'update' ?>"/>
    <? if (!empty($att->id)) echo '<input type="hidden" name="id" value="' . $att->id . '">' ?>
    <input type="hidden" name="flight_day" value="<?= $flight_day->format('Y-m-d') ?>"/>
    <input type="hidden" name="user_id" value="<?= empty($att->id) ? $_SESSION["user_id"] : $att->user_id ?>"/>
    <input type="hidden" name="plane_selection"/>
    <input type="hidden" name="pos_longitude"/>
    <input type="hidden" name="pos_latitude"/>
    <input type="hidden" name="is_planned" class="<?= empty($att->id) && $today == $flight_day && !$is_manual ? 'set_by_location' : '' ?>"
        value="<?= ($att->is_planned || ($today < $flight_day)) ?>"/>
    <div class="row g-3">
        <ul class="form-group col-md-7">
            <label><?= empty($att->id) ? 'Was möchtest du fliegen?' : 'Flugzeugauswahl:' ?></label>
            <div class="list-group">
                <?
                foreach($planes as $plane)
                {
                    ?>
                    <label class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <input class="form-check-input me-1 plane_btn" type="checkbox" <? if (in_array($plane->id, $plane_selection)) echo 'checked' ?> data-plane_id="<?= $plane->id ?>">
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
        </ul>
        <div class="col">
            <div class="row g-3">
                <? if ($is_manual) { ?>
                    <div class="form-group col-12">
                        <label>Name:</label>
                        <input name="manual_entry" type="text" class="form-control" required placeholder="Der Name der Person die du eintragen möchtest" value="<?= $att->manual_entry ?>">
                        <small class="form-text text-muted">Um jemand anderes manuell in die Liste einzutragen (optional).</small>
                    </div>
                <? } ?>
                <div class="form-group col-12">
                    <label><?= empty($att->id) ? 'Als' : 'Rolle' ?>:</label>
                    <select name="role" class="form-select">
                        <?
                        foreach(ATTENDANCE_ROLES as $key => $role)
                        {
                            ?><option value="<?= $key ?>" <? if ($att->role == $key) echo 'selected' ?>><?= $role["name"] ?></option><?
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-12 <?= !empty($att->id) && empty($att->is_planned) && $today == $flight_day ? 'd-none' : '' ?>">
                    <label>Wann etwa <?= $is_manual ? 'wird er/sie' : 'wirst du' ?> da sein?</label>
                    <div class="input-group">
                        <span class="input-group-text" id="eta-lbl">ETA</span>
                        <input name="time" type="time" class="form-control" value="<?= isset($att->time) ? $att->time->format('H:i:s') : '' ?>" placeholder="ETA" aria-label="ETA" aria-describedby="eta-lbl">
                        <button type="button" class="btn btn-danger btn_unset input-group-text"><i class="fas fa-times"></i></span>
                    </div>
                    <small class="form-text text-muted">optional</small>
                </div>
                <div class="form-group col-12">
                    <div class="form-check">
                        <label class="form-check-label">
                            <input name="first" type="hidden" value="<?= !empty($att->first) ? 1 : 0 ?>">
                            <input class="form-check-input hidden_input" type="checkbox" <? if (!empty($att->first)) echo 'checked'; ?>>
                            Letztes Mal nicht dran gewesen <i class="far fa-frown"></i>
                        </label>
                    </div>
                </div>
                <?
                if (empty($att->id) && $today == $flight_day && !$is_manual)
                {
                    ?>
                    <div class="col-12 position_error">
                        <div class="alert alert-danger m-0" role="alert">
                            Dein Browser erlaubt nicht die Übertragung deiner Position oder die Positionsdaten sind nicht verfügbar.
                        </div>
                    </div>
                    <div class="col-12 distance_valid">
                        <div class="alert alert-success m-0" role="alert">
                            Du bist nah genug am Flugplatz um dich als <b>anwesend</b> einzutragen.
                            <div class="form-group pt-2">
                                <button type="submit" class="form-control btn btn-primary">Trag mich ein!</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 distance_invalid">
                        <div class="alert alert-warning m-0" role="alert">
                            <i class="fas fa-map-marker-alt"></i>
                            Du bist noch <strong class="distance">(unbekannt) m</strong> zu weit vom Flugplatz entfernt um dich als <b>anwesend</b> einzutragen.
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