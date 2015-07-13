/*
	table のオートフィルタ
	http://neko.dosanko.us/script/auto-filter/
	2006-12-4 版
*/

function classAutofilter() {
  // 設定 ここから↓

  this.LABEL_FILTER_BLANK = "(空欄)";
  this.LABEL_FILTER_ALL = "すべて";

  this.VAL_FILTER_ALL = "__all__";

  // 設定 ここまで↑

  this.elmTbody = null; // 復元用 tbody
} // classAutofilter //


// ----- public な関数として扱う -----


//
// フィルタ生成
//
//	@param  strId オートフィルタを表示する table の ID
//	@return       状態 (1:フィルタ生成済み、0:実行完了、-1:未対応ブラウザ)
//
classAutofilter.prototype.Create_Filter = function(strId) {
  if (this.elmTbody != null) return 1;

  if (!document.getElementById || !document.removeChild) return -1;

  var elmTable = document.getElementById(strId);
  var elmTbody = elmTable.getElementsByTagName("tbody").item(0);
  var elmTr_body = elmTbody.getElementsByTagName("tr");
  var elmTr_filter = this._Create_Element({
    element: "tr"
  });

  // table の内容取得とフィルタ表示用の要素生成
  var arrayCols = new Array();
  var numLen_tr = elmTr_body.length;
  for (var numRow = 0; numRow < numLen_tr; numRow++) {
    var alias_childNodes = elmTr_body[numRow].childNodes;
    for (var numCol = 0; numCol < alias_childNodes.length; numCol++) {
      var alias_col = alias_childNodes[numCol];

      if (alias_col.nodeType != 1) {
        // 要素以外を取り除く
        elmTr_body[numRow].removeChild(alias_col);
        numCol--;
        continue;
      } // if //

      if (numRow == 0) {
        var elmTh_head = this._Create_Element({
          element: "th",
          attr: {
            scope: "row"
          }
        });
        elmTr_filter.appendChild(elmTh_head);

        arrayCols[numCol] = new Array();
      } // if //

      arrayCols[numCol][numRow] = this._Get_TextContent(alias_col);
    } // for //
  } // for //
  this.elmTbody = elmTbody.cloneNode(true);

  // フィルタを 1 つずつ追加していくとカクカクとした表示となるため
  // “display : none”にして追加を行う
  var elmThead = elmTable.getElementsByTagName("thead").item(0);
  elmTr_filter.style.display = "none";
  elmThead.appendChild(elmTr_filter);
  this._Rewrite_Filter(elmTr_filter, arrayCols);
  try {
    elmTr_filter.style.display = "table-row";
  } catch (e) {
    elmTr_filter.style.display = "block";
  } // try //

  return 0;
}; // classAutofilter.prototype.Create_Filter //

//
// フィルタを適用
//
//	@param elmSelect select 要素
//
classAutofilter.prototype.Select_Filter = function(elmSelect) {
  var arrayFilters = new Array(); // 現在選択されているフィルタの値
  var boolAll = true; // 全フィルタが VAL_FILTER_ALL か
  var elmTr_filter = elmSelect.parentNode.parentNode;
  var elm_select = elmTr_filter.getElementsByTagName("select");
  var numLen_select = elm_select.length;
  for (var numCol = 0; numCol < numLen_select; numCol++) {
    arrayFilters[numCol] = this._Get_SelectValue(elm_select[numCol]);

    if (arrayFilters[numCol] != this.VAL_FILTER_ALL) {
      // 全フィルタを VAL_FILTER_ALL にしたとき
      // 少しでも早く表示を元に戻すため、全フィルタをチェック

      boolAll = false;
    } // if //
  } // for //

  var elmTbody_new = this.elmTbody.cloneNode(true);
  var elmTr_body = elmTbody_new.getElementsByTagName("tr");
  var numRow = 0;
  if (!boolAll) {
    // いずれかのフィルタで VAL_FILTER_ALL 以外を選択

    for (numRow = 0; numRow < elmTr_body.length; numRow++) {
      var alias_row = elmTr_body[numRow];
      var numLen_childNodes = alias_row.childNodes.length;
      for (numCol = 0; numCol < numLen_childNodes; numCol++) {
        if (arrayFilters[numCol] == this.VAL_FILTER_ALL) {
          continue;
        } // if //

        var strCell = this._Get_TextContent(alias_row.childNodes[numCol]);
        if (strCell == null) strCell = ""; // 空欄

        if (arrayFilters[numCol] != this.VAL_FILTER_ALL && arrayFilters[numCol] != strCell) {
          // フィルタ適用

          elmTbody_new.removeChild(alias_row);
          numRow--;
          break;
        } // if //
      } // for //
    } // for //
  } // if //

  var arrayCols = new Array(); // フィルタ用の列データ
  var numLen_tr = elmTr_body.length;
  for (numRow = 0; numRow < numLen_tr; numRow++) {
    var alias_row = elmTr_body[numRow];
    var numLen_childNodes = alias_row.childNodes.length;
    for (numCol = 0; numCol < numLen_childNodes; numCol++) {
      if (arrayCols[numCol] == null) {
        arrayCols[numCol] = new Array();
      } // if //
      arrayCols[numCol][numRow] = this._Get_TextContent(alias_row.childNodes[numCol]);
    } // for //
  } // for //

  this._Rewrite_Filter(elmTr_filter, arrayCols);

  var elmThead = elmTr_filter.parentNode;
  var elmTable = elmThead.parentNode;
  var elmTbody = elmTable.getElementsByTagName("tbody").item(0);
  elmTable.removeChild(elmTbody);
  elmTable.appendChild(elmTbody_new);
}; // classAutofilter.prototype.Select_Filter //

//
// フィルタ生成時の比較
//
//	@param  val_a 比較対象 A
//	@param  val_b 比較対象 B
//	@return       比較結果
//
classAutofilter.prototype.Compare_Filter = function(val_a, val_b) {
  if (!isNaN(val_a) && !isNaN(val_b)) {
    val_a = Number(val_a);
    val_b = Number(val_b);
  } // if //

  return (val_a < val_b) ? 1 : (val_a > val_b) ? -1 : 0;
}; // classAutofilter.prototype.Compare_Filter //


// ----- private な関数として扱う -----


//
// フィルタの select 要素の生成/書き換え
//
//	@param arrayCols 列データ
//
classAutofilter.prototype._Rewrite_Filter = function(elmTr_filter, arrayCols) {
  var elm_select = elmTr_filter.getElementsByTagName("select");

  if (elm_select.length == 0) elm_select = null;

  var numLen_arrayCols = arrayCols.length;
  for (var numCol = 0; numCol < numLen_arrayCols; numCol++) {
    var alias_cols = arrayCols[numCol];

    alias_cols.sort(this.Compare_Filter);

    var elmSelect_new = (elm_select != null) ? elm_select[numCol].cloneNode(false) : this._Create_Element({
      element: "select"
    });
    var class_pointer = this;
    elmSelect_new.onchange = function() {
      class_pointer.Select_Filter(this);
    };

    var strSelect = null;
    var elmOption = this._Create_Element({
      element: "option",
      attr: {
        value: this.VAL_FILTER_ALL
      },
      content: this.LABEL_FILTER_ALL
    });
    if (elm_select == null) {
      elmOption.defaultSelected = true;
      elmOption.selected = true;
    } else {
      strSelect = this._Get_SelectValue(elm_select[numCol]);
      if (strSelect == this.VAL_FILTER_ALL) {
        elmOption.defaultSelected = true;
        elmOption.selected = true;
      } // if //
    } // if //
    elmSelect_new.appendChild(elmOption);

    var numLen_cols = alias_cols.length;
    for (var i = 0; i < numLen_cols; i++) {
      if (i > 0 && alias_cols[i] != alias_cols[i - 1] || i == 0) {
        var alias_col = alias_cols[i];

        var strValue = null;
        var strContent = null;
        if (alias_col != null && alias_col.length > 0) {
          // セルが空欄以外

          strValue = alias_col;
          strContent = strValue;
        } else {
          // セルが空欄

          strValue = "";
          strContent = this.LABEL_FILTER_BLANK;
          alias_col = strValue;
        } // if //
        elmOption = this._Create_Element({
          element: "option",
          attr: {
            value: strValue
          },
          content: strContent
        });
        if (strSelect == alias_col) {
          elmOption.defaultSelected = true;
          elmOption.selected = true;
        } // if //
        elmSelect_new.appendChild(elmOption);
      } // if //
    } // for //

    if (elm_select != null) {
      elmTr_filter.childNodes[numCol].removeChild(elm_select[numCol]);
    } // if //
    elmTr_filter.childNodes[numCol].appendChild(elmSelect_new);
  } // for //
}; // classAutofilter.prototype._Rewrite_Filter //

//
// 要素生成
//
//	@param  argv 要素の情報
//	@return      生成された要素
//
classAutofilter.prototype._Create_Element = function(argv) {
  var elm = document.createElement(argv.element);

  if (argv.attr) {
    var alias_attr = argv.attr;

    for (var i in alias_attr) {
      // ブラウザによっては動作に問題のある setAttribute を用いない

      elm[i] = alias_attr[i];
    } // for //
  } // if //

  if (argv.content) {
    var nodeText = document.createTextNode(argv.content);
    elm.appendChild(nodeText);
  } // if //

  return elm;
}; // classAutofilter.prototype._Create_Element //

//
// select 要素で選択されている value 属性値を取得
//
//	@param  elmSelect select 要素
//	@return           選択されている value 属性値
//
classAutofilter.prototype._Get_SelectValue = function(elmSelect) {
  return elmSelect.options[elmSelect.selectedIndex].value;
}; // classAutofilter.prototype._Get_SelectValue //

//
// 要素からテキストのみ取得
//
//	@param  elm 要素
//	@return     テキスト
//
classAutofilter.prototype._Get_TextContent = function(elm) {
  return (typeof(elm.textContent) != "undefined") ? elm.textContent : elm.innerText;
}; // classAutofilter.prototype._Get_TextContent //


// ===== ボタンの処理 =========================================


//
// オートフィルタを表示するボタン
//
//	@param elmInput ボタンの要素
//	@param strId    オートフィルタを表示する table の ID
//
function Button_DispFilter(elmInput, strId) {
  // “display : none”を用いてオートフィルタの表示/非表示を切り替えると
  // Opera 8.54 では一度非表示にしたあと select 要素で選択されている物を
  // 変更しようとしても変更できなくなる
  // そのため“表示ボタン”の使用は一度限りとし、ボタンは自滅させる

  var strMes = "お使いのブラウザではオートフィルタを表示できません";

  this.boolExec = false;
  var objInterval = setInterval(
    function() {
      clearInterval(objInterval);
      (function() {
        if (this.boolExec) return;

        this.boolExec = true;

        var cAutofilter = new classAutofilter();
        if (cAutofilter.Create_Filter(strId) < 0) {
          alert(strMes);
          return;
        } // if //

        // ボタンの自滅
        var elm_parent = elmInput.parentNode;
        elm_parent.removeChild(elmInput);
      })();
    }, // function //
    200
  );
} // Button_DispFilter //

/*
	table のソート 2
	http://neko.dosanko.us/script/sort_table2/
	2006-12-4 版

	とほほのWWW入門の「テーブルをソートする(2003/2/2版)」がベース
	http://www.tohoho-web.com/wwwxx038.htm
*/

function classSortTable() {
  // 設定 ここから↓

  this.MES_ALERT = "お使いのブラウザではソート機能を利用できません";

  this.ORDER_DEFAULT = -1;

  // 設定 ここまで↑

  this.numOrder = this.ORDER_DEFAULT; // 現在のソート方向
  this.arrayColumn = null; // 現在ソートを行っている列の優先順位
  this.arrayCol_Last = new Array(); // 最後にソートした列
} // classSortTable //

var g_cSortTable = new classSortTable();


// ----- public な関数として扱う -----


//
// ソートボタン
//
//	@param strId_table 対象 table の id
//	@param arrayColumn ソート条件とする列(第一、第二…とソートする優先順に配列で指定)
//
classSortTable.prototype.Button_Sort = function(strId_table, arrayColumn) {
  var class_pointer = this;

  class_pointer.boolExec = false;
  var objInterval = setInterval(
    function() {
      clearInterval(objInterval);
      (function() {
        if (class_pointer.boolExec) return;
        class_pointer.boolExec = true;

        class_pointer._Sort_Table(strId_table, arrayColumn);
      })();
    }, // function //
    200
  );
}; // classSortTable.prototype.Button_Sort //

//
// 行の比較
//
//	@param  elmTr_a 比較対象の行 A
//	@param  elmTr_b 比較対象の行 B
//	@return         比較結果
//
classSortTable.prototype.Compare = function(elmTr_a, elmTr_b) {
  var arrayColumn = this.arrayColumn; // 優先順位
  var numOrder = this.numOrder; // ソート方向
  var numResult = 0; // 比較結果

  var val_a = null; // 行 A のセルの値
  var val_b = null; // 行 B のセルの値

  var numLen_arrayColumn = arrayColumn.length;
  for (var i = 0; i < numLen_arrayColumn && numResult == 0; i++) {
    var alias_arrayColumn = arrayColumn[i];

    if (typeof(elmTr_a.textContent) != "undefined") {
      val_a = elmTr_a.childNodes[alias_arrayColumn].textContent;
      val_b = elmTr_b.childNodes[alias_arrayColumn].textContent;
    } else {
      val_a = elmTr_a.childNodes[alias_arrayColumn].innerText;
      val_b = elmTr_b.childNodes[alias_arrayColumn].innerText;
    } // if //

    if ((!isNaN(val_a) || val_a.length < 1) && (!isNaN(val_b) || val_b.length < 1)) {
      // セルが両方とも“数字か空白”なら数値としてソート
      // Number.NEGATIVE_INFINITY を空白の代替とする

      val_a = (val_a.length > 0) ? Number(val_a) : Number.NEGATIVE_INFINITY;
      val_b = (val_b.length > 0) ? Number(val_b) : Number.NEGATIVE_INFINITY;
    } // if //

    if (val_a < val_b) {
      numResult = (numOrder == -1) ? 1 : -1;
    } else if (val_a > val_b) {
      numResult = (numOrder != -1) ? 1 : -1;
    } // if //
  } // for //

  return numResult;
}; // classSortTable.prototype.Compare //


// ----- private な関数として扱う -----


//
//	ソート
//
//	@param strId_table 対象 table の id
//	@param arrayColumn ソート条件とする列(第一、第二…とソートする優先順に配列で指定)
//
classSortTable.prototype._Sort_Table = function(strId_table, arrayColumn) {
  // 対象外ブラウザのチェック
  if (!document.getElementById || !document.removeChild) {
    alert(this.MES_ALERT);
    return;
  } // if //

  var elmTable = document.getElementById(strId_table);
  var elmTbody = elmTable.getElementsByTagName("tbody").item(0);
  var elmTr = elmTbody.getElementsByTagName("tr");
  var arrayTr = new Array();

  // 現在の内容を取得
  var numLen_tr = elmTr.length;
  for (var i = 0; i < numLen_tr; i++) {
    var alias_tr = elmTr[i];
    var alias_childNodes = alias_tr.childNodes;
    for (var j = 0; j < alias_childNodes.length; j++) {
      if (alias_childNodes[j].nodeType != 1) {
        // 要素以外を取り除く
        alias_tr.removeChild(alias_childNodes[j]);
        j--;
      } // if //
    } // for //

    arrayTr[i] = alias_tr.cloneNode(true);
  } // for //

  var alias_arrayCol_Last = this.arrayCol_Last;
  if (alias_arrayCol_Last[strId_table] == null) alias_arrayCol_Last[strId_table] = -1;

  // 同じ列のソートは、ソート方向を反転
  this.numOrder = (alias_arrayCol_Last[strId_table] == arrayColumn[0]) ? this.numOrder * -1 : this.ORDER_DEFAULT;

  this.arrayColumn = arrayColumn;
  alias_arrayCol_Last[strId_table] = arrayColumn[0];

  var class_pointer = this;
  arrayTr.sort(function(elm_a, elm_b) {
    return class_pointer.Compare(elm_a, elm_b);
  });

  // ソート結果
  var elmTbody_result = elmTbody.cloneNode(false);
  var numLen_tr = arrayTr.length;
  for (i = 0; i < numLen_tr; i++) {
    elmTbody_result.appendChild(arrayTr[i]);
  } // for //
  elmTable.removeChild(elmTbody)
  elmTable.appendChild(elmTbody_result);
}; // classSortTable.prototype._Sort_Table //
