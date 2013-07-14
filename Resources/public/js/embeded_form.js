jQuery(document).ready(function () {
    // Get the ul that holds the collection of embeded
    var collectionHolder = $('ul.embeded');

    // setup an "add" link
    var $addLink = $('<a href="#" class="btn btn-success">Add</a>');
    var $newLinkLi = $('<li></li>').append($addLink);

    // add a delete link to all of the existing embeded form li elements
    collectionHolder.find('li').each(function () {
        addEmbededFormDeleteLink($(this));
    });

    // add the "add" anchor and li to the embeded ul
    collectionHolder.append($newLinkLi);

    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    collectionHolder.data('index', collectionHolder.find(':input').length);

    $addLink.on('click', function (e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();

        // add a new embeded form (see next code block)
        addEmbededForm(collectionHolder, $newLinkLi);
    });
});

function addEmbededForm(collectionHolder, $newLinkLi) {
    // Get the data-prototype explained earlier
    var prototype = collectionHolder.data('prototype');

    // get the new index
    var index = collectionHolder.data('index');

    // Replace '__name__' in the prototype's HTML to
    // instead be a number based on how many items we have
    var newForm = prototype.replace(/__name__/g, index);

    // increase the index with one for the next item
    collectionHolder.data('index', index + 1);

    // Display the form in the page in an li, before the "Add" link li
    var $newFormLi = $('<li></li>').append(newForm);
    $newLinkLi.before($newFormLi);

    // add a delete link to the new form
    addEmbededFormDeleteLink($newFormLi);
}

function addEmbededFormDeleteLink($embededFormLi) {
    removeId = 'remove_' + $embededFormLi.children().eq(0).attr('id');
    var $removeFormA = $('<div id="' + removeId + '" class="remove-button"><a href="#"><i class="icon-remove-circle"></i></a></div><div class="clear"></div>');
    $embededFormLi.append($removeFormA);

    $removeFormA.on('click', function (e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();

        // remove the li for the embeded form
        $embededFormLi.remove();
    });
}