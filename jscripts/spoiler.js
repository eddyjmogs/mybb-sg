$(function (e) {
    "use strict";
    e('<style type="text/css">.sceditor-dropdown { text-align: ' + ("rtl" === e("body").css("direction") ? "right" : "left") + "; }.sceditor-button-spoiler div  { background: url(images/spoiler/spoiler.png) no-repeat; }</style>").appendTo(
        "body"
    ),
        e.sceditor.command.set("spoiler", {
            _dropDown: function (i, t, o) {
                var s;
                (s = e(
                    '<div><div><label for="spoilertitle">' +
                        i._("Spoiler Title:") +
                        '</label> <input type="text" id="spoilertitle" value="" /></div><div><label for="spoilerdesc">' +
                        i._("Spoiler Content:") +
                        '</label> <textarea type="text" id="spoilerdesc" /></div><div><input type="button" class="button" value="' +
                        i._("Insert") +
                        '" /></div><div>'
                )),
                    setTimeout(function () {
                        s.find("#spoiler").focus();
                    }, 100),
                    s.find(".button").click(function (e) {
                        var t = s.find("#spoilertitle").val();
                        if ("" !== t)
                            var r = s.find("#spoilerdesc").val(),
                                l = "[spoiler=" + t + "]",
                                d = "[/spoiler]";
                        else
                            var r = s.find("#spoilerdesc").val(),
                                l = "[spoiler]",
                                d = "[/spoiler]";
                        o ? ((l += o), (d = d)) : "" !== t ? i.insert("[spoiler=" + t + "]" + r + "[/spoiler]") : i.insert("[spoiler]" + r + "[/spoiler]"), i.closeDropDown(!0), e.preventDefault();
                    }),
                    i.createDropDown(t, "insertspoiler", s.get(0));
            },
            exec: function (i) {
                e.sceditor.command.get("spoiler")._dropDown(this, i);
            },
            txtExec: function (i) {
                e.sceditor.command.get("spoiler")._dropDown(this, i);
            },
            tooltip: "Spoiler Content:",
        });
});
