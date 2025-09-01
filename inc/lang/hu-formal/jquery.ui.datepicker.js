/* Hungarian initialisation for the jQuery UI date picker plugin. */
( function( factory ) {
	"use strict";

	if ( typeof define === "function" && define.amd ) {

		// AMD. Register as an anonymous module.
		define( [ "../widgets/datepicker" ], factory );
	} else {

		// Browser globals
		factory( jQuery.datepicker );
	}
} )( function( datepicker ) {
"use strict";

datepicker.regional.hu = {
	closeText: "Bezárás",
	prevText: "Vissza",
	nextText: "Előre",
	currentText: "Ma",
	monthNames: [ "január", "február", "március", "április", "május", "június",
	"július", "augusztus", "szeptember", "október", "november", "december" ],
	monthNamesShort: [ "jan.", "febr.", "márc.", "ápr.", "máj.", "jún.",
	"júl.", "aug.", "szept.", "okt.", "nov.", "dec." ],
	dayNames: [ "Vasárnap", "Hétfő", "Kedd", "Szerda", "Csütörtök", "Péntek", "Szombat" ],
	dayNamesShort: [ "V", "H", "K", "Sze", "Cs", "P", "Szo" ],
	dayNamesMin: [ "V", "H", "K", "Sze", "Cs", "P", "Szo" ],
	weekHeader: "Hét",
	dateFormat: "yy.mm.dd.",
	firstDay: 1,
	isRTL: false,
	showMonthAfterYear: true,
	yearSuffix: "." };
datepicker.setDefaults( datepicker.regional.hu );

return datepicker.regional.hu;

} );
