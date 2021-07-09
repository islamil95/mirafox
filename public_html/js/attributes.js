/**
 * Рекурсивный нисходящий поиск атрибута в дереве по идентификатору
 *
 * @param id Идентификатор атрибута
 * @param attributes Атрибуты в которых ищем (при первом запуске без этого параметра)
 * @returns {*}
 */
function findInTreeRecursive(id, attributes) {
	if (attributes === undefined) {
		attributes = window.attributesTree;
	}

	for (var i in attributes) {
		if (attributes.hasOwnProperty(i)) {
			if (attributes[i].id == id) {
				return attributes[i];
			}
			if (attributes[i].children && attributes[i].children.length) {
				var findedChild = findInTreeRecursive(id, attributes[i].children);
				if (findedChild) {
					return findedChild;
				}
			}
		}
	}
}

/**
 * Рекурсивный поиск всех идентификаторов родителей заданного атрибута
 *
 * @param id Идентификатор атрибута
 * @param parents Накопительный список родителей (первый вызов без этого параметра)
 * @returns {*}
 */
function findAllParents(id, parents) {
	if (parents === undefined) {
		parents = [];
	}
	var attribute = findInTreeRecursive(id);
	if (attribute !== undefined) {
		if (attribute.parent_id > 0) {
			parents.push(attribute.parent_id);
			return findAllParents(attribute.parent_id, parents);
		}
	}
	return parents;
}

/**
 * Рекурсивный поиск идентификаторов всех дочерних атрибутов
 *
 * @param id Идентификатор атрибута
 * @param childrenIds Накопительный массив идентифкаторов дочерних атрибутов (при первом запуске без него)
 * @returns {*}
 */
function findAllChildrenIds(id, childrenIds) {
	if (childrenIds === undefined) {
		childrenIds = [];
	}
	var attribute = findInTreeRecursive(id);
	if (attribute && attribute.children && attribute.children.length) {
		for (var i in attribute.children) {
			if (attribute.children.hasOwnProperty(i)) {
				childrenIds.push(attribute.children[i].id);
			}
			childrenIds.concat(findAllChildrenIds(attribute.children[i].id, childrenIds));
		}
	}

	return childrenIds;
}
