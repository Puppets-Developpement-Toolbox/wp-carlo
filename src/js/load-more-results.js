(function () {
	
	//------------DOMLOAD		
	document.addEventListener("DOMContentLoaded", function () {

    //load more results
    var moreBtn = document.querySelector('#js-more-results');
    if(moreBtn) {
      //no-more-results !
      if(document.querySelector('#end')) {
        //remove btn
        if(moreBtn) {
          moreBtn.remove();
        }
      }
      else {
        if(moreBtn) {
          moreBtn.addEventListener('click', loadMoreResults);
        }
      }
    }

    var resultsPagination = 1;


    document.querySelectorAll('#js-filters-form input').forEach(function(input) {
      input.addEventListener('change', resetResults)
    })

    function resetResults() {
      document.querySelector('.actus-list').innerHTML = '';
      resultsPagination = 1;
      loadResults();
    }

    function loadMoreResults() {
      resultsPagination++;
      loadResults();
    }

    function loadResults() {
      var loader = document.querySelector('#js-more-loader');
      var formulaire = document.querySelector('#js-filters-form');
      var params = new FormData(formulaire);
      params.append('page', resultsPagination);
      loader.classList.add('visible');
      var btn = document.querySelector('#js-more-results');
			btn.style.display = 'none';
      var url = btn.getAttribute('data-action');
			var method = formulaire.getAttribute('method') || 'post';
			XXL.ajaxCall(url, params, method, loadMoreResultsCallback);
    }

    function loadMoreResultsCallback(response) {
      var newList = document.querySelector('.actus-list');
      newList.innerHTML += response;
      var loader = document.querySelector('#js-more-loader');
      loader.classList.remove('visible');
      var btn = document.querySelector('#js-more-results');
      //no-more-results !
      if(newList.querySelector('#end')) {
        //remove btn
        btn.style.display = 'none';
      } else {
        btn.style.display = '';
      }
    }

	});
})();
