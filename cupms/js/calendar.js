/**
 * @author Malkovsky Nikolay
 * TODO Replace this file to where it should be.
 */

var calendar = {
	pipeBirthday : "2007-10-23",

	months : ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь",
		"Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"],

	/**
	 * Be carefull, days and months should be counted from 1.
	 * @return True if the given data is correct, false otherwise.
	 */
	isCorrect : function(Year, Month, Day) {
		var isLeap = ((Year % 4 == 0 && Year % 100 != 0) || Year % 400 == 0);

		var Days = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

		if(isLeap) {
			Days[1] = 29;
		}

		return(Day <= Days[Month - 1]);
	},

	today : function() {
		var temp = new Date();
		var result = temp.getFullYear() + '-';

		if(temp.getMonth() < 9) {
			result += '0';
		}
		result += (temp.getMonth() + 1) + "-";

		if(temp.getDate() < 10) {
			result += '0';
		}
		result += temp.getDate();

		return result;
	}
}