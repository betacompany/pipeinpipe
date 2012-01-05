/**
 * @author Malkovsky Nikolay
 */


function Stack() {
	return {
		head : null,

		push : function(data) {
			var node = new __List();
			node.next = this.head;
			node.data = data;
			this.head = node;
		},

		top : function() {
			return this.head.data;
		},

		pop : function() {
			if(this.head != null) this.head = this.head.next;
		},

		empty : function () {
			return this.head == null;
		}
	}
}

/**
 * @author Artyom Grigoriev
 */
function Queue() {
	return {
		head: null,
		tail: null,

		add: function (data) {
			var node = new __List();
			node.data = data;
			if (this.tail == null) {
				this.head = this.tail = node;
			} else {
				this.tail.next = node;
				this.tail = node;
			}
		},

		get: function () {
			var result = this.head.data;
			if (this.head.next != null) {
				this.head = this.head.next;
			} else {
				this.head = this.tail = null;
			}
			return result;
		},

		print: function () {
			var node = this.head;
			var result = '';
			while (node != null) {
				result += node.data + ' -> ';
				node = node.next;
			}
			return result;
		},

		empty: function () {
			return this.head == null;
		}
	}
}

function __List() {
	return {
		next : null,
		data : null
	};
}

/**
 * @author Malkovsky Nikolay.
 */
function SuffixTree(stringArray) {
	if(stringArray == undefined) stringArray = [];

	var result = {
		/**
		 * @brief Array containing original string set.
		 */
		stringArray : stringArray,

		/**
		 * @brief root node.
		 */
		root : new __SuffixTreeNode(),

		/**
		 * @brief Adds string from stringArray with index <tt>index</tt> to the tree.
		 *
		 * @param index index in stringArray to get string to add.
		 */
		addString : function (index) {
			if (index < 0 || index >= stringArray.length) {
				return;
			}

			var temp;
			var str = stringArray[index];
			for (var i = 0; i < str.length; i++) {
				temp = this.root;
				for (var j = i; j < str.length; j++) {
					temp = temp.addChild(str.charAt(j));
				}
				var v = new __List();
				v.next = temp.index;
				v.data = index;
				temp.index = v;
			}
		},

		/**
		 * @brief Array which contains indexes of strings in stringArray containing
		 * str as substring.
		 */
		getPossibleIndexes : function (str) {
			if(str == undefined) {
				return [];
			}
			var tempArray = new Array(stringArray.length);
			var resultArray = new Array();
			var i;

			for (i = 0; i < resultArray.length; ++i) {
				resultArray = false;
			}

			var temp = this.root;

			for (i = 0; i < str.length; ++i) {
				temp = temp.findChild(str.charAt(i));
				if (temp == null) {
					return resultArray;
				}
			}
			if(temp.index != null) {
				var v = temp.index;
				while(v != null) {
					tempArray[v.data] = true;
					resultArray.push(v.data);
					v = v.next;
				}
			}
			if(temp.child != null) {
				temp.child.dfs(resultArray, tempArray);
			}

			return resultArray;
		}
	};

	for (var i = 0; i < stringArray.length; ++i) {
		result.addString(i);
	}


	return result;
}

function __SuffixTreeNode() {

	return {
		//TODO probably change this field to some set and remove "next" field(as it is part of the list structure)
		child : null,

		next : null,

		//TODO equals to terminal symbol by default
		data : "#",

		//TODO change symbol if "#" is in use.
		terminal : "#",

		/**
		 * @brief list of possible indexes of strings ending on current suffix.
		 */
		index : null,

		/**
		 * @brief Finds child of node with data = symbol or creates new one with such data.
		 * TODO Should be private.
		 * @param symbol - char to find in set of childs of node.
		 */
		addChild : function (symbol) {
			var res;
			var subtree;

			if (this.child == null) { //если потомков нет, добавляем первый
				subtree = new __SuffixTreeNode();
				subtree.data = symbol;
				this.child = subtree;

				res = subtree;

			} else { //если есть, то добавляем, как в список
				if (symbol < this.child.data) {
					subtree = new __SuffixTreeNode();
					subtree.next = this.child;
					subtree.data = symbol;
					this.child = subtree;

					res = subtree;
				} else if (symbol == this.child.data) {
					res = this.child;
				} else {
					var temp = this.child;

					while (1) {

						if ((temp.next == null) || (symbol < temp.next.data)) {
							subtree = new __SuffixTreeNode();
							subtree.data = symbol;
							subtree.next = temp.next;
							temp.next = subtree;

							res = subtree;

							break;
						}

						if (symbol == temp.next.data) { //если потомок есть, то возвращаем указатель на него
							res = temp.next;
							break;
						}

						temp = temp.next;

					}
				}
			}

			return res;
		},

		/**
		 * @brief Finds if current node has a child with data <tt>symbol</tt>
		 *
		 * @param symbol letter to find.
		 *
		 * @return currnet node's child with data equals to <tt>symbol</tt>.
		 */
		findChild : function (symbol) {
			var res;

			if (this.child == null) {
				return null;
			} else {
				if (symbol < this.child.data) {
					return null;
				} else if (symbol == this.child.data) {
					res = this.child;
				} else {
					var temp = this.child;

					while (1) {

						if ((temp.next == null) || (symbol < temp.next.data)) {
							return null;
						}

						if (symbol == temp.next.data) {
							res = temp.next;
							break;
						}

						temp = temp.next;
					}
				}
			}

			return res;
		},

		dfs : function (resultArray, tempArray) {
			if(this.index != null) {
				var v = this.index;
				while(v != null) {
					if(!tempArray[v.data]) {
						resultArray.push(v.data);
						tempArray[v.data] = true;
					}
					v = v.next;
				}
			}

			if(this.child != null) {
				this.child.dfs(resultArray, tempArray);
			}

			if(this.next != null) {
				this.next.dfs(resultArray, tempArray);
			}
		}
	};
}

