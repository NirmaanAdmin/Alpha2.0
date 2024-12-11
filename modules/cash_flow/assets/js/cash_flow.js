"use-strict";
$(function() {
    init_expenses_total();
    $("#stats-top").removeClass("hide");
    $("body.expenses").on("click", "a.toggle-small-view", function() {
        $(".table-expenses")
            .DataTable()
            .column(5)
            .visible(false, false)
            .column(7)
            .visible(false, false)
            .column(9)
            .visible(false, false)
            .ajax.reload();
    });
});