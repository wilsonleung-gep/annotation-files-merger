/*global YUI */
/*jslint regexp: true */
YUI.add(
    "gep_results_panel",

    function(Y) {
        "use strict";

        var resultsPanel = function(fieldID, options) {
            var GEP = Y.GEP,
                Util = GEP.util,
                EM = GEP.eventManager,
                settings,
                panel,
                browserLinkTpl;

            settings = Util.mergeSettings(options, {
                downloadLinkID: "mergedFileLink",
                browserLinkID: "browserLink",
                dbSelectBoxID: "browserLinkSelect",
                browserLinkSection: "browserLinkSection",
                browserSelectSection: "browserSelectSection",
                dbAnchor: "browserLinkAnchor",
                checklistPanelID: "checklistPanel",
                checklistContainerID: "checklistContainer",
                overlapContainerID: "overlapContainer",
                unselected: "_unselected",
                checklistTableID: "checklistTable"
            });

            panel = Y.byID(fieldID);

            function initDbSelectBox() {
                var dbLink = Y.byID(settings.dbAnchor),
                    dbSelectBox = Y.byID(settings.dbSelectBoxID);

                dbLink.hide();
                dbSelectBox.show();

                dbSelectBox.on("change", function () {
                    var db = dbSelectBox.get("value");

                    if (db === settings.unselected) {
                        dbLink.hide();
                    } else {
                        dbLink.set("href", Y.Lang.sub(browserLinkTpl, { dbname: db }));
                        dbLink.show();
                    }
                });
            }

            function initDbSelectSection(results) {
                var dbLink = Y.byID(settings.dbAnchor),
                    selectSection = Y.byID(settings.browserSelectSection),
                    linkSection = Y.byID(settings.browserLinkSection),
                    ucscDb = results.ucscDb;

                if (ucscDb === null) {
                    linkSection.hide();
                    initDbSelectBox();

                } else {
                    selectSection.hide();

                    dbLink.set("href", Y.Lang.sub(browserLinkTpl, { dbname: ucscDb }));
                    dbLink.show();
                }
            }

            function initCheckList(checklist) {
                var checklistTable,
                    rowTpl,
                    tableBody;

                checklistTable = Y.Node.create(
                    '<table id="' + settings.checklistTableID + '" class="checklistTable">' +
          "<thead><tr><th>Gene</th><th>Status</th><th>Message</th></tr></thead>" +
          "<tbody></tbody>" +
        "</table>");

                rowTpl = "<tr class='{status}'><td>{criteria}</td>" +
        "<td><b>{status}</b></td><td>{message}</td></tr>";

                tableBody = checklistTable.one("tbody");

                Y.each(checklist, function(item) {
                    tableBody.append(Y.Lang.sub(rowTpl, item));
                });

                Y.byID(settings.checklistContainerID).append(checklistTable);

                Y.byID(settings.checklistPanelID).removeClass("hidden");
            }

            function initOverlapList(overlap) {
                var overlapList = Y.Node.create("<ul></ul>"),
                    rowTpl = "<li>{description}</li>",
                    overlapSection = Y.byID(settings.overlapContainerID);

                Y.each(overlap, function(item) {
                    overlapList.append(Y.Lang.sub(rowTpl, { description: item }));
                });

                overlapSection.append(overlapList);
                overlapSection.replaceClass("hidden", "warning");
            }

            function showGFFResults(results) {
                browserLinkTpl = Y.Lang.sub(
                    GEP.data.browserRoot + "hgt.customText={webpath}&position={position}&db={dbname}",
                    results);

                initDbSelectSection(results);

                initCheckList(results.checklist);

                if ((results.overlap) && (results.overlap.length > 0)) {
                    initOverlapList(results.overlap);
                }

                Y.byID(settings.browserLinkID).removeClass("hidden");
            }

            function showVcfResults(results) {
                browserLinkTpl = Y.Lang.sub(
                    GEP.data.browserRoot +
                        "hgt.customText={customText}&position={position}&db={dbname}",
                    results);

                initDbSelectSection(results);

                Y.byID(settings.browserLinkID).removeClass("hidden");
            }

            function buildDownloadLink(webpath) {
                var m = webpath.match(/([^\/]+)$/);

                if (m === null) {
                    return webpath;
                }

                return Y.GEP.data.trashDirectory + "/" + m[1];
            }

            EM.subscribe("showMergeResults", function(results) {
                var webpath;

                try {
                    if (results.status === "failure") {
                        throw new Error(results.message);
                    }

                    webpath = buildDownloadLink(results.webpath);

                    Y.byID(settings.downloadLinkID).set("href", webpath);

                    if (webpath.match(/\.gff$/)) {
                        showGFFResults(results);
                    }

                    if (webpath.match(/\.vcf\.txt$/)) {
                        showVcfResults(results);
                    }

                    panel.removeClass("hidden");

                } catch (e) {
                    EM.fire("showMessage", {
                        type: "error",
                        message: e.message || "The GEP service is down. Please try again later."
                    });
                }
            });

            return panel;
        };

        Y.namespace("GEP").resultsPanel = resultsPanel;
    },
    "0.0.1", {
        requires: ["gep_event_manager", "json-parse"]
    });
