/**
 * Ensemble des variables et fonctions de configuration / défaut
 * @namespace nextdom.private
 */
var init = function (_param, _default) {
  return (typeof _param == 'number') ? _param : (typeof _param != 'boolean' || _param) && (_param !== false && _param || _default || '');
};


nextdom.private = {
  /**
   * Paramètres par défaut de toutes les fonctions de l'API
   * Ces valeurs sont merges avec les paramètres appelés à chaque appel de fonction
   * @example defaultqueryParams = {
   *      async : true,         // Appel AJAX synchrone (deprecated) ou non
   *      type : 'POST',        // Transmission des données
   *      dataTye : 'json',     // Type de données échangées
   *      error : nextdom.private.fn_error, // Callback en cas d'erreur
   *      success : function (_data) {      // Callback en cas de succès
   *          return _data;
   *      },
   *      complete : function () {}        // Callback quoi qu'il se passe
   * };
   */
  defaultqueryParams: {
    async: true,
    type: 'POST',
    dataType: 'json',
    pre_success: function (_data) {
      return _data;
    },
    success: function (_data) {
      console.log(_data);
    },
    post_success: function (_data) {
    },
    complete: function () {
    },
    error: function (_data) {
      // Erreur dans l'API ou mauvais retour AJAX (appel de ajax::error() côté PHP)
      console.log(_data);
    }
  },
  /**
   * Objet retourné quand tout s'est bien passé
   */
  API_end_successful: 'API\'s call went alright, AJAX is running or ended if {async : false} ! Doesn\'t mean it\'s going to work as expected... It depends on your parameters, none traitment has been made.',
  code: 42

};

/**
 * String to help user know what's going on
 */
var no_error_code = 'No error code has been sent.';
var no_result = '';
var code = 42;

/**
 * Fonction de conversion du retour AJAX en cas d'erreur en objet pour la fonction d'erreur
 */
nextdom.private.handleAjaxErrorAPI = function (_request, _status, _error) {
  if (_request.status && _request.status != '0') {
    if (_request.responseText) {
      return {type: 'AJAX', code: code, message: _request.responseText};
    } else {
      return {type: 'AJAX', code: code, message: _request.status + ' : ' + _error};
    }
  }
  return {type: 'AJAX', code: code, message: 'Unknown error'};
};


/**
 * Retourne les paramètres AJAX de l'API en fonction des paramètres choisis par l'utilisateur
 */
nextdom.private.getAjaxParams = function (queryParams, target, action) {
  // cas particulier du type dans les paramètres
  var typeInData = false;

  // si type est dans les paramètres et est différent de POST ou GET
  if ($.inArray(queryParams.type, ['POST', 'GET']) === -1) {
    typeInData = true;
    queryParams.data = queryParams.data || {};
    queryParams._type = queryParams.type; // on stocke la donnée
    queryParams.type = 'POST'; // post par défaut
  }

  var ajaxParams = {
    type: queryParams.type,
    dataType: queryParams.dataType,
    async: queryParams.async,
    global: queryParams.global,
    error: function (_request, _status, _error) {
      queryParams.error(nextdom.private.handleAjaxErrorAPI(_request, _status, _error));
    },
    success: function (data) {
      data = queryParams.pre_success(data);
      if (data.state != 'ok') {
        queryParams.error({
          type: 'PHP',
          message: data.result || 'Error - ' + no_result || '',
          code: data.code || no_error_code || ''
        });
      } else {
        // On envoie les données à l'utilisateur, tout s'est bien passé
        // Il récupère l'objet qu'il a demandé directement
        var result = init(data.result, no_result);

        if (data.result === false) {
          result = false;
        }

        queryParams.success(result);
      }
      queryParams.post_success(data);
    },
    complete: queryParams.complete,
    data: {}
  };

  if (target !== undefined) {
    ajaxParams.url = 'src/ajax.php';
    ajaxParams.data = {
      target: target,
      action: action
    };
  }

  if (typeInData) {
    ajaxParams.data.type = queryParams._type;
  }

  return ajaxParams; // return
};
/**
 * Fonction qui va checker si la valeur d'un paramètre vérifie une regexp
 * C'est récursif pour les arrays et les objets pris en value
 * DOIT ETRE ENCADRÉE D'UN TRY { } CATCH (e) {}
 * @param {Object} queryParams
 * @param queryParams.value Valeur du paramètre à tester
 * @param {Object} queryParams.regexp Regexp à vérifier
 * @param {string} [queryParams.name] Nom du paramètre à tester [optionnel]
 */

// tests en console :
// try { nextdom.private.checkParamsValue({value : [{test : 'check', test2 :'eeee'},{test : 'oefop', test2 : 'kfefe', test3 : 10}], regexp : /a|e|ch|1|zec/}); } catch(e) { console.log(e); }
nextdom.private.checkParamValue = function (queryParams) {
  try {
    checkParamsRequired(queryParams, ['value', 'regexp']);
  } catch (e) {
    throw {
      type: 'API',
      code: code,
      message: 'Une erreur est présente dans l\'API SARA JS. Les paramètres spécifiés dans checkParamValue ne sont pas complets. ' + e.message
    };
  }

  var value = queryParams.value;
  var regexp = queryParams.regexp;
  var name = queryParams.name || 'One parameter';

  if (typeof value == 'object') {
    // appel récursif pour les Array et les Objets
    for (var i in value) {
      checkParamValue({
        name: name,
        value: value[i],
        regexp: regexp
      });
    }
  } else {
    value += ''; // on convertie la valeur en string

    // pour faire un inArray, utiliser la regexp : /mot1|mot2|mot3|mot4/
    if (regexp.test(value) === false) {
      throw {
        type: 'API',
        code: code,
        message: name + ' isn\'t correct (doesn\'t match : ' + regexp.toString() + '). `' + value + '` received.'
      };
    }
  }
};


/** Fonction qui permet de vérifier que tous les paramètres obligatoires ont bien été renseignés dans l'objet params
 * Note : chaque fonction doit appeler cette fonction au préalable après avoir créé un string[] composé des paramètres requis.
 * @return {Object} ret Contient les résultats du check
 * @return {boolean} ret.result Nous renseigne si les paramètres requis ont bien été remplis
 * @return {Object[]} ret.missing Ensemble des options qui n'ont pas été renseignées, optionnelles ou non
 * @return {string} ret.missing.name Nom d'un paramètre manquant
 * @return {boolean} ret.missing.optional Caractère optionnel ou non du paramètre manquant
 * @return {number} ret.missing.group Groupe associé au paramètre (0 pour les paramètres obligatoires et n pour les paramètres optionnels, ce numéro est identique pour les membres d'un même groupe, il faut qu'au moins l'un d'entre eux soit précisé pour que la fonction fonctionne)
 * @return {string} ret.missing.toString Renvoie un paramètre manquant sous forme de string pour l'affichage
 */
nextdom.private.checkParamsRequired = function (queryParams, queryParamsRequired) {
  var missings = Array();
  var group = Array();
  var missingAtLeastOneParam = false;
  var optionalGroupNumber = 0;

  for (var key in queryParamsRequired) {
    if (typeof queryParamsRequired[key] === 'object') {
      optionalGroupNumber++;

      // il y a plusieurs clés, il faut qu'au moins l'une d'entre elles soit présente
      var ok = false;
      for (var key2 in queryParamsRequired[key]) {
        if (queryParams.hasOwnProperty(queryParamsRequired[key][key2])) {
          ok = true;
          // pas de break, on veut savoir quels paramètres sont présents et lesquels ne le sont pas.
        } else {
          missings.push({
            name: queryParamsRequired[key][key2],
            optional: true,
            group: {
              id: optionalGroupNumber
            }
          });
        }
      }

      // on indique si le groupe a été check ou pas
      group[optionalGroupNumber] = {
        checked: ok
      };

      // de manière plus globale, on indique s'il manque un paramètre obligatoire ou pas
      if (!ok) {
        missingAtLeastOneParam = true;
      }
    } else if (!queryParams.hasOwnProperty(queryParamsRequired[key])) {
      missings.push({
        name: queryParamsRequired[key],
        optional: false,
        group: {
          id: 0,
          checked: false
        }
      });
      missingAtLeastOneParam = true;
    }
  }

  if (missingAtLeastOneParam) {
    var tostring = 'Parameters missing : ';
    for (var i in missings) {
      var miss = missings[i];
      tostring += miss.name + ' ';

      // dans le cas des paramètres optionnels, on rajoute une information pour savoir si le groupe d'options (optionnels donc) a été rempli (via une autre option non manquante donc) ou non
      var checkedstring = miss.optional && (group[miss.group.id].checked) ? 'yes' : 'no' || '';

      tostring += (miss.optional) ? '[optional, group=' + miss.group.id + ' checked=' + checkedstring + ']' : '[needed]';
      tostring += ', ';
    }

    // on enlève la virgule et l'espace en trop
    tostring = tostring.substring(0, tostring.length - 2);
    throw {
      type: 'API',
      code: code,
      message: tostring
    };
  }
  return;
};

/**
 * Check global
 * À impérativement encadrer de try {} catch () {}
 */
nextdom.private.checkAndGetParams = function (queryParams, queryParamsSpecifics, queryParamsRequired) {
  // throw une exception en cas d'erreur (à attraper plus haut)
  nextdom.private.checkParamsRequired(queryParams, queryParamsRequired || []);

  // tout est ok, on merge avec les paramètres par défaut + les spécifiques à la fonction
  var params = $.extend({}, nextdom.private.defaultqueryParams, queryParamsSpecifics, queryParams || {});

  // on json_encode tous les objets contenus dans les params
  for (var attr in params) {
    var param = params[attr];
    params[attr] = (typeof param == 'object') ? json_encode(param) : param;
  }

  var ajaxParams = nextdom.private.getAjaxParams(params);

  return {
    params: params,
    ajaxParams: ajaxParams
  };
};

/**
 * Fonction générique qui permet de checker les valeurs des paramètres
 */
nextdom.private.checkParamsValue = function (queryParams) {
  if (Object.prototype.toString.call(queryParams) == '[object Object]') {
    nextdom.private.checkParamValue(queryParams);
  } else {
    for (var i in queryParams) {
      nextdom.private.checkParamValue(queryParams[i]);
    }
  }
};

nextdom.private.isValidQuery = function (queryParams, requiredParams, specificParams) {
  try {
    nextdom.private.checkParamsRequired(queryParams, requiredParams);
  } catch (e) {
    (queryParams.error || specificParams.error || nextdom.private.defaultqueryParams.error)(e);
    return false;
  }
  return true;
};