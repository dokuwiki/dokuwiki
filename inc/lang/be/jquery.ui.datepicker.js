/* Belarussian (UTF-8) initialisation for the jQuery UI date picker plugin. */
/* Written by Andrew Stromnov (stromnov@gmail.com). */
( function( factory ) {
	if ( typeof define === "function" && define.amd ) {

		// AMD. Register as an anonymous module.
		define( [ "../widgets/datepicker" ], factory );
	} else {

		// Browser globals
		factory( jQuery.datepicker );
	}
}( function( datepicker ) {

datepicker.regional.be = {
	closeText: "Зачыніць",
	prevText: "&#x3C;Папя",
	nextText: "Наст&#x3E;",
	currentText: "Сёння",
	monthNames: [ "Студзень","Люты","Сакавік","Красавік","Май","Чэрвень",
	"Ліпень","Жнівень","Верасень","Кастрычнік","Лістапад","Снежань" ],
	monthNamesShort: [ "Сту","Лют","Сак","Кра","Май","Чэр",
	"Ліп","Жні","Вер","Кас","Ліс","Сне" ],
	dayNames: [ "нядзеля","панядзелак","аўторак","серада","чацвер","пятніца","субота" ],
	dayNamesShort: [ "ндз","пнд","атр","срд","чцв","птн","сбт" ],
	dayNamesMin: [ "Нд","Пн","Ат","Ср","Чц","Пт","Сб" ],
	weekHeader: "Тыд",
	dateFormat: "dd.mm.yy",
	firstDay: 1,
	isRTL: false,
	showMonthAfterYear: false,
	yearSuffix: "" };
datepicker.setDefaults( datepicker.regional.be );

return datepicker.regional.be;

} ) );
