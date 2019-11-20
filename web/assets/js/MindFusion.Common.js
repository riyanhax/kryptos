(function(a, c, b) {
    if (typeof module != "undefined") {
        module.exports = b()
    } else {
        if (typeof define == "function" && typeof define.amd == "object") {
            define(a, c, b)
        } else {
            this[a] = b()
        }
    }
}("MindFusion.Common", [], function() {
    var MsAjaxImpl = {
        registerNamespace: function(namespace) {
            Type.registerNamespace(namespace)
        },
        registerClass: function(type, typeName, baseType, interfaceTypes) {
            if (baseType == "Control") {
                baseType = Sys.UI.Control
            }
            if (Sys.__registeredTypes && Sys.__registeredTypes[typeName]) {
                Sys.__registeredTypes[typeName] = false
            } else {
                if (window.__registeredTypes && window.__registeredTypes[typeName]) {
                    window.__registeredTypes[typeName] = false
                }
            }
            var prms = [typeName];
            if (baseType !== undefined) {
                prms.push(baseType);
                if (interfaceTypes) {
                    if (typeof interfaceTypes == "string") {
                        try {
                            interfaceTypes = eval(interfaceTypes);
                            prms.push(interfaceTypes)
                        } catch (err) {}
                    } else {
                        prms.push(interfaceTypes)
                    }
                }
            }
            Type.prototype.registerClass.apply(type, prms)
        },
        registerDisposableObject: function(instance) {
            Sys.Application.registerDisposableObject(instance)
        },
        initializeBase: function(type, instance, baseArguments) {
            type.initializeBase(instance, baseArguments)
        },
        callBaseMethod: function(type, instance, name, baseArguments) {
            return type.callBaseMethod(instance, name, baseArguments)
        },
        isInstanceOfType: function(type, instance) {
            return type.isInstanceOfType(instance)
        },
        parseType: function(typeName) {
            return Type.parse(typeName)
        },
        inheritsFrom: function(type, baseType) {
            return type.inheritsFrom(baseType)
        },
        createControl: function(type, properties, events, references, element) {
            return Sys.Component.create(type, properties, events, references, element)
        },
        findControl: function(id, parent) {
            return Sys.Application.findComponent(id, parent)
        },
        addHandler: function(instance, eventName, handler) {
            instance.eventHandlers.addHandler(eventName, handler)
        },
        getHandler: function(instance, eventName) {
            return instance.eventHandlers.getHandler(eventName)
        },
        removeHandler: function(instance, eventName, handler) {
            if (!instance) {
                return
            }
            if (instance.eventHandlers) {
                instance.eventHandlers.removeHandler(eventName, handler)
            } else {
                if (instance._events) {
                    $removeHandler(instance, eventName, handler)
                }
            }
        },
        eventHandlerList: function() {
            return new Sys.EventHandlerList()
        },
        addHandlers: function(element, events, handlerOwner, autoRemove) {
            $addHandlers(element, events)
        },
        clearHandlers: function(element) {
            $clearHandlers(element)
        },
        createDelegate: function(instance, method) {
            return Function.createDelegate(instance, method)
        },
        createCallback: function(method, context) {
            return Function.createCallback(method, context)
        },
        getBounds: function(element) {
            var bounds = Sys.UI.DomElement.getBounds(element);
            return new MindFusion.Drawing.Rect(bounds.x, bounds.y, bounds.width, bounds.height)
        },
        fromJson: function(json) {
            return Sys.Serialization.JavaScriptSerializer.deserialize(json)
        },
        toJson: function(instance) {
            return Sys.Serialization.JavaScriptSerializer.serialize(instance)
        },
        ajaxRequest: function(sender, url, data, callback) {
            var request = new XMLHttpRequest();
            thisObj = sender;
            request.onreadystatechange = function() {
                if (request.readyState == 4 && request.status == 200) {
                    var responseData = mflayer.fromJson(mflayer.fromJson(request.responseText).d);
                    callback.apply(sender, [responseData])
                }
            };
            request.open("POST", url);
            request.setRequestHeader("Content-Type", "application/json; charset=utf-8");
            request.send(data)
        }
    };
    var JQueryImpl = {
        registerNamespace: function(namespace) {
            var root = window;
            var parts = namespace.split(".");
            for (var i = 0; i < parts.length; i++) {
                var part = parts[i];
                var ns = root[part];
                if (!ns) {
                    ns = root[part] = {}
                }
                root = ns
            }
        },
        registerClass: function(type, typeName, baseType, interfaceTypes) {
            if (baseType == "Control") {
                baseType = MindFusion.Dom.Control
            }
            type.prototype.constructor = type;
            type.__typeName = typeName;
            if (baseType) {
                type.__baseType = baseType;
                for (var memberName in baseType.prototype) {
                    var memberValue = baseType.prototype[memberName];
                    if (!type.prototype[memberName]) {
                        type.prototype[memberName] = memberValue
                    }
                }
            }
        },
        registerDisposableObject: function(instance) {
            $(window).on("unload", function() {
                instance.dispose()
            })
        },
        initializeBase: function(type, instance, baseArguments) {
            var baseType = type.__baseType;
            if (baseType) {
                if (!baseArguments) {
                    baseType.apply(instance)
                } else {
                    baseType.apply(instance, baseArguments)
                }
            }
        },
        callBaseMethod: function(type, instance, name, baseArguments) {
            var baseType = type.__baseType;
            if (baseType) {
                var baseMethod = baseType.prototype[name];
                if (baseMethod) {
                    if (!baseArguments) {
                        return baseMethod.apply(instance)
                    } else {
                        return baseMethod.apply(instance, baseArguments)
                    }
                }
            }
        },
        isInstanceOfType: function(type, instance) {
            if (!instance) {
                return false
            }
            if (instance instanceof type) {
                return true
            }
            var baseType = instance.constructor.__baseType;
            while (baseType) {
                if (baseType === type) {
                    return true
                }
                baseType = baseType.__baseType
            }
            return false
        },
        parseType: function(typeName) {
            if (!typeName) {
                return null
            }
            var fn = eval(typeName);
            if (typeof(fn) == "function") {
                return fn
            }
        },
        inheritsFrom: function(type, baseType) {
            var bType = type.__baseType;
            while (bType) {
                if (bType === baseType) {
                    return true
                }
                bType = bType.__baseType
            }
            return false
        },
        createControl: function(type, properties, events, references, element) {
            var control = new type(element, properties);
            control._element = element;
            this.registerDisposableObject(control);
            $(element).data("MindFusion", control);
            control.initialize();
            return control
        },
        findControl: function(id, parent) {
            var element;
            if (parent) {
                element = $(parent).children("#" + id)
            } else {
                element = $("#" + id)
            }
            if (element) {
                return element.data("MindFusion")
            }
            return null
        },
        getEvent: function(instance, eventName, create) {
            if (instance.eventHandlers[eventName] == undefined) {
                if (!create) {
                    return null
                }
                instance.eventHandlers[eventName] = []
            }
            return instance.eventHandlers[eventName]
        },
        addHandler: function(instance, eventName, handler) {
            if (!instance.eventHandlers) {
                instance.eventHandlers = this.eventHandlerList()
            }
            var eventList = this.getEvent(instance, eventName, true);
            eventList.push(handler)
        },
        getHandler: function(instance, eventName) {
            var event = this.getEvent(instance, eventName);
            if (!event || (event.length === 0)) {
                return null
            }
            return function(source, args) {
                var i = event.length;
                while (i--) {
                    event[i](source, args)
                }
            }
        },
        removeHandler: function(instance, eventName, handler) {
            if (!instance) {
                return
            }
            if (instance.eventHandlers) {
                var event = this.getEvent(instance, eventName);
                if (!event) {
                    return
                }
                var index = event.indexOf(handler);
                if (index > -1) {
                    event.splice(index, 1)
                }
            } else {
                $(instance).unbind(eventName, handler)
            }
        },
        eventHandlerList: function() {
            return new MindFusion.Collections.Dictionary()
        },
        addHandlers: function(element, events, handlerOwner, autoRemove) {
            for (var e in events) {
                $(element).bind(e, events[e])
            }
        },
        clearHandlers: function(element) {
            $(element).unbind()
        },
        createDelegate: function(instance, method) {
            return $.proxy(method, instance)
        },
        createCallback: function(method, context) {
            return function() {
                var l = arguments.length;
                if (l > 0) {
                    var args = [];
                    for (var i = 0; i < l; i++) {
                        args[i] = arguments[i]
                    }
                    args[l] = context;
                    return $.proxy(method.apply(this, args), context)
                }
                return $.proxy(method, context)
            }
        },
        getBounds: function(element) {
            var location = $(element).offset();
            var jqVersion = $.fn.jquery;
            var arr = jqVersion.split(".");
            if ((arr[0] == 1 && arr[1] < 9)) {
                var bodyBorderTop = parseFloat($.css(document.body, "borderTopWidth")) || 0;
                var bodyBorderLeft = parseFloat($.css(document.body, "borderLeftWidth")) || 0;
                location.top += bodyBorderTop;
                location.left += bodyBorderLeft
            }
            return new MindFusion.Drawing.Rect(location.left, location.top, $(element).width(), $(element).height())
        },
        fromJson: function(json) {
            if (JSON) {
                return JSON.parse(json)
            } else {
                throw new Error("JSON is undefined.")
            }
        },
        toJson: function(instance) {
            if (JSON) {
                return JSON.stringify(instance)
            } else {
                throw new Error("JSON is undefined.")
            }
        },
        ajaxRequest: function(sender, url, data, callback) {
            $.ajax({
                type: "POST",
                url: url,
                data: data,
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                context: sender,
                success: function(data) {
                    var responseData = mflayer.fromJson(data.d);
                    callback.apply(this, [responseData])
                },
                error: function() {
                    // alert("Ajax error")
                }
            })
        }
    };
    var StandAloneImpl = {
        registerNamespace: function(namespace) {
            var root = window;
            var parts = namespace.split(".");
            for (var i = 0; i < parts.length; i++) {
                var part = parts[i];
                var ns = root[part];
                if (!ns) {
                    ns = root[part] = {}
                }
                root = ns
            }
        },
        registerClass: function(type, typeName, baseType, interfaceTypes) {
            if (baseType == "Control") {
                baseType = MindFusion.Dom.Control
            }
            type.prototype.constructor = type;
            type.__typeName = typeName;
            if (baseType) {
                type.__baseType = baseType;
                for (var memberName in baseType.prototype) {
                    var memberValue = baseType.prototype[memberName];
                    if (!type.prototype[memberName]) {
                        type.prototype[memberName] = memberValue
                    }
                }
            }
        },
        registerDisposableObject: function(instance) {
            window.addEventListener("unload", function() {
                instance.dispose()
            })
        },
        initializeBase: function(type, instance, baseArguments) {
            var baseType = type.__baseType;
            if (baseType) {
                if (!baseArguments) {
                    baseType.apply(instance)
                } else {
                    baseType.apply(instance, baseArguments)
                }
            }
        },
        callBaseMethod: function(type, instance, name, baseArguments) {
            var baseType = type.__baseType;
            if (baseType) {
                var baseMethod = baseType.prototype[name];
                if (baseMethod) {
                    if (!baseArguments) {
                        return baseMethod.apply(instance)
                    } else {
                        return baseMethod.apply(instance, baseArguments)
                    }
                }
            }
        },
        isInstanceOfType: function(type, instance) {
            if (!instance) {
                return false
            }
            if (instance instanceof type) {
                return true
            }
            var baseType = instance.constructor.__baseType;
            while (baseType) {
                if (baseType === type) {
                    return true
                }
                baseType = baseType.__baseType
            }
            return false
        },
        parseType: function(typeName) {
            if (!typeName) {
                return null
            }
            var fn = eval(typeName);
            if (typeof(fn) == "function") {
                return fn
            }
        },
        inheritsFrom: function(type, baseType) {
            var bType = type.__baseType;
            while (bType) {
                if (bType === baseType) {
                    return true
                }
                bType = bType.__baseType
            }
            return false
        },
        createControl: function(type, properties, events, references, element) {
            if (!this.MindFusionControls) {
                this.MindFusionControls = new MindFusion.Collections.Dictionary()
            }
            var control = new type(element, properties);
            control._element = element;
            this.registerDisposableObject(control);
            this.MindFusionControls.set(element.id, control);
            control.initialize();
            return control
        },
        findControl: function(id, parent) {
            var element = document.getElementById(id);
            if (element) {
                try {
                    var control = this.MindFusionControls.get(element.id);
                    return control
                } catch (err) {
                    return null
                }
            }
            return null
        },
        getEvent: function(instance, eventName, create) {
            if (instance.eventHandlers[eventName] == undefined) {
                if (!create) {
                    return null
                }
                instance.eventHandlers[eventName] = []
            }
            return instance.eventHandlers[eventName]
        },
        addHandler: function(instance, eventName, handler) {
            if (!instance.eventHandlers) {
                instance.eventHandlers = this.eventHandlerList()
            }
            var eventList = this.getEvent(instance, eventName, true);
            eventList.push(handler)
        },
        getHandler: function(instance, eventName) {
            var event = this.getEvent(instance, eventName);
            if (!event || (event.length === 0)) {
                return null
            }
            return function(source, args) {
                var i = event.length;
                while (i--) {
                    event[i](source, args)
                }
            }
        },
        removeHandler: function(instance, eventName, handler) {
            if (!instance) {
                return
            }
            if (instance.eventHandlers) {
                var event = this.getEvent(instance, eventName);
                if (!event) {
                    return
                }
                var index = event.indexOf(handler);
                if (index > -1) {
                    event.splice(index, 1)
                }
            } else {
                instance.removeEventListener(eventName, handler)
            }
        },
        eventHandlerList: function() {
            return new MindFusion.Collections.Dictionary()
        },
        addHandlers: function(element, events, handlerOwner, autoRemove) {
            for (var e in events) {
                element.addEventListener(e, events[e])
            }
        },
        clearHandlers: function(element) {
            for (var e in element.eventHandlers) {
                element.removeEventListener(element.eventHandlers[e], element.eventHandlers[e])
            }
        },
        createDelegate: function(instance, method) {
            var delegate = function() {
                return method.apply(instance, arguments)
            };
            return delegate
        },
        createCallback: function(method, context) {
            var thisObj = this;
            return function() {
                var l = arguments.length;
                if (l > 0) {
                    var args = [];
                    for (var i = 0; i < l; i++) {
                        args[i] = arguments[i]
                    }
                    args[l] = context;
                    return thisObj.createDelegate(context, method.apply(this, args))
                }
                return thisObj.createDelegate(context, method)
            }
        },
        getBounds: function(element) {
            var rect = element.getBoundingClientRect();
            var documentElement = element.ownerDocument.documentElement;
            var bodyElement = element.ownerDocument.body;
            var x = Math.round(rect.left) + (documentElement.scrollLeft || bodyElement.scrollLeft);
            var y = Math.round(rect.top) + (documentElement.scrollTop || bodyElement.scrollTop);
            return new MindFusion.Drawing.Rect(x, y, rect.width, rect.height)
        },
        fromJson: function(json) {
            if (JSON) {
                return JSON.parse(json)
            } else {
                throw new Error("JSON is undefined.")
            }
        },
        toJson: function(instance) {
            if (JSON) {
                return JSON.stringify(instance)
            } else {
                throw new Error("JSON is undefined.")
            }
        },
        ajaxRequest: function(sender, url, data, callback) {
            var request = new XMLHttpRequest();
            thisObj = sender;
            request.onreadystatechange = function() {
                if (request.readyState == 4 && request.status == 200) {
                    var responseData = mflayer.fromJson(mflayer.fromJson(request.responseText).d);
                    callback.apply(sender, [responseData])
                }
            };
            request.open("POST", url);
            request.setRequestHeader("Content-Type", "application/json; charset=utf-8");
            request.send(data)
        }
    };
    if (typeof $break == "undefined") {
        $break = {}
    }
    if (typeof MindFusionImpl == "undefined") {
        MindFusionImpl = "JQuery"
    }
    var checkImplementation = function(implementation) {
        for (var f in MsAjaxImpl) {
            if (MsAjaxImpl[f] instanceof Function) {
                if (implementation[f] == undefined || !(implementation[f] instanceof Function)) {
                    throw new Error("Abstract layer implementation does not implement interface member " + f)
                }
            }
        }
        return true
    };
    if (MindFusionImpl == "MsAjax") {
        mflayer = MsAjaxImpl
    } else {
        if (MindFusionImpl == "JQuery") {
            mflayer = JQueryImpl
        } else {
            if (MindFusionImpl == "StandAlone") {
                mflayer = StandAloneImpl
            } else {
                if (MindFusionImpl instanceof Object) {
                    if (checkImplementation(MindFusionImpl)) {
                        mflayer = MindFusionImpl
                    }
                } else {
                    if (typeof MindFusionImpl == "string") {
                        var impl = eval(MindFusionImpl);
                        if (checkImplementation(impl)) {
                            mflayer = impl
                        }
                    }
                }
            }
        }
    }
    mflayer.registerNamespace("MindFusion");
    MindFusion.AbstractionLayer = mflayer;
    MindFusion.registerNamespace = function(namespace) {
        MindFusion.AbstractionLayer.registerNamespace(namespace)
    };
    MindFusion.registerClass = function(type, typeName, baseType, interfaceTypes) {
        MindFusion.AbstractionLayer.registerClass(type, typeName, baseType, interfaceTypes)
    };
    MindFusion.find = function(id) {
        return MindFusion.AbstractionLayer.findControl(id)
    };
    MindFusion.registerNamespace("MindFusion.Dom");
    (function(mdom) {
        var Control = mdom.Control = function(element) {
            this._element = element
        };
        Control.prototype = {
            dispose: function() {},
            get_element: function() {
                return this._element
            }
        };
        MindFusion.registerClass(Control, "MindFusion.Dom.Control")
    })(MindFusion.Dom);
    if (!MindFusion.EventArgs || !MindFusion.EventArgs.__typeName) {
        MindFusion.EventArgs = function() {
            mflayer.initializeBase(MindFusion.EventArgs, this)
        };
        MindFusion.EventArgs.prototype = {};
        MindFusion.registerClass(MindFusion.EventArgs, "MindFusion.EventArgs");
        MindFusion.EventArgs.Empty = new MindFusion.EventArgs()
    }
    if (!MindFusion.CancelEventArgs || !MindFusion.CancelEventArgs.__typeName) {
        MindFusion.CancelEventArgs = function() {
            mflayer.initializeBase(MindFusion.CancelEventArgs, this)
        };
        MindFusion.CancelEventArgs.prototype = {
            get_cancel: function() {
                return this._cancel
            },
            set_cancel: function(value) {
                this._cancel = value
            }
        };
        MindFusion.registerClass(MindFusion.CancelEventArgs, "MindFusion.CancelEventArgs", MindFusion.EventArgs)
    }
    if (!MindFusion.Builder || !MindFusion.Builder.__typeName) {
        MindFusion.Builder = function(prototypeClass, diagram, instance) {
            mflayer.initializeBase(MindFusion.Builder, this);
            this.prototypeClass = prototypeClass;
            this.diagram = diagram;
            this.instance = instance;
            this.generate()
        };
        MindFusion.Builder.prototype = {
            generate: function() {
                var getters = Object.keys(this.prototypeClass);
                getters = getters.filter(function(item) {
                    return MindFusion.Builder.isGetSetter(getters, item)
                });
                var funcs = {};
                for (var key in getters) {
                    var name = getters[key].split("get")[1];
                    var prop = name.charAt(0).toLowerCase() + name.slice(1);
                    var fbody = "var propName = '" + prop + "' + 'Value'; var prop2Name = '" + prop + "' + 'Assigned'; var funcName = 'set' + '" + name + "'; this[propName] = value; this[prop2Name] = true; if (this.instance != null) 	this.instance[funcName](value); return this;";
                    this[prop] = new Function("value", fbody);
                    if (prop == "font") {
                        fbody = "var propName = '" + prop + "' + 'Value'; var prop2Name = '" + prop + "' + 'Assigned'; var funcName = 'set' + '" + name + "'; if (arguments.length == 2) { value = new MindFusion.Drawing.Font(name, size); }else value = arguments[0]; this[propName] = value; this[prop2Name] = true; if (this.instance != null) 	this.instance[funcName](value); return this;";
                        this[prop] = new Function("name", "size", fbody)
                    }
                    if (prop == "brush") {
                        fbody = "var propName = '" + prop + "' + 'Value'; var prop2Name = '" + prop + "' + 'Assigned'; var funcName = 'set' + '" + name + "'; if (arguments.length == 3) { value = {type:'LinearGradientBrush', color1:color1, color2: color2, angle:coord1};}else if (arguments.length == 4) { value = {type:'RadialGradientBrush', color1:color1, color2: color2, radius1: coord1, radius2: coord2};}else value = arguments[0]; this[propName] = value; this[prop2Name] = true; if (this.instance != null) 	this.instance[funcName](value); return this;";
                        this[prop] = new Function("color1", "color2", "coord1", "coord2", fbody)
                    }
                }
            },
            create: function() {
                var typeName = this.prototypeClass.getType();
                var type = mflayer.parseType(typeName);
                if (!type) {
                    return null
                }
                var obj = new type(this.diagram);
                var assignedValues = Object.keys(this);
                assignedValues = assignedValues.filter(function(item) {
                    return MindFusion.Builder.isAssignment(assignedValues, item)
                });
                for (var key in assignedValues) {
                    var name = assignedValues[key].split("Assigned")[0];
                    var value = this[name + "Value"];
                    var func = "set" + name.charAt(0).toUpperCase() + name.slice(1);
                    if (obj[func]) {
                        obj[func](value)
                    }
                }
                return obj
            },
            setInstance: function(instance) {
                this.instance = instance;
                var assignedValues = Object.keys(this);
                assignedValues = assignedValues.filter(function(item) {
                    return MindFusion.Builder.isAssignment(assignedValues, item)
                });
                for (var key in assignedValues) {
                    var name = assignedValues[key].split("Assigned")[0];
                    delete this[assignedValues[key]];
                    delete this[name + "Value"]
                }
            }
        };
        MindFusion.Builder.isGetSetter = function(list, item) {
            if (item.indexOf("get") !== 0) {
                return false
            }
            var name = item.split("get")[1];
            var setter = "set" + name;
            return list.indexOf(setter) > -1
        };
        MindFusion.Builder.isAssignment = function(list, item) {
            if (item.indexOf("Assigned") == -1) {
                return false
            }
            var name = item.split("Assigned")[0];
            var prop = name + "Value";
            return list.indexOf(prop) > -1
        };
        MindFusion.registerClass(MindFusion.Builder, "MindFusion.Builder")
    }
    MindFusion.registerNamespace("MindFusion.Collections");
    (function(mcol) {
        var ArrayList = mcol.ArrayList = function() {
            var array = new Array();
            var Utilities = mcol.Utilities;
            array.indexOf = Utilities.indexOf;
            array.remove = Utilities.remove;
            array.contains = Utilities.contains;
            array.any = Utilities.any;
            array.all = Utilities.all;
            array.forEach = Utilities.forEach;
            array.forReverse = Utilities.forReverse;
            return array
        };
        ArrayList.indexOf = function(array, element) {
            return array.indexOf(element)
        };
        ArrayList.add = function(array, element) {
            array.push(element)
        };
        ArrayList.insert = function(array, index, element) {
            array.splice(index, 0, element)
        };
        ArrayList.remove = function(array, element) {
            var index = array.indexOf(element);
            if (index > -1) {
                array.splice(index, 1);
                return true
            }
            return false
        };
        ArrayList.removeAt = function(array, index) {
            if (index > -1) {
                array.splice(index, 1);
                return true
            }
            return false
        };
        ArrayList.contains = function(array, element) {
            return array.indexOf(element) > -1
        };
        ArrayList.forEach = function(array, func, instance) {
            for (var i = 0, l = array.length; i < l; i++) {
                var element = array[i];
                if (typeof(element) !== "undefined") {
                    func.call(instance, element, i, array)
                }
            }
        };
        ArrayList.clone = function(array) {
            return array.slice(0)
        };
        ArrayList.addRange = function(array, items) {
            for (var i = 0, l = items.length; i < l; i++) {
                array.push(items[i])
            }
        }
    })(MindFusion.Collections);
    (function(mcol) {
        var Dictionary = mcol.Dictionary = function() {
            this.table = new mcol.HashTable()
        };
        Dictionary.prototype.set = function(key, value) {
            var entry = this.table.get(key);
            if (entry == null) {
                entry = this.table.add(key)
            }
            entry.value = value
        };
        Dictionary.prototype.get = function(key) {
            var entry = this.table.get(key);
            if (entry != null) {
                return entry.value
            }
            throw new Error("Cannot find key " + key)
        };
        Dictionary.prototype.contains = function(key) {
            return this.table.contains(key)
        };
        Dictionary.prototype.remove = function(key) {
            return this.table.remove(key)
        };
        Dictionary.prototype.getCount = function() {
            return this.table.count
        };
        Dictionary.prototype.forEach = function(func, thisRef) {
            this.table.forEach(function(entry) {
                func.call(thisRef, entry.key, entry.value)
            })
        };
        Dictionary.prototype.forEachValue = function(func, thisRef) {
            this.table.forEach(function(entry) {
                func.call(thisRef, entry.value)
            })
        };
        Dictionary.prototype.forEachKey = function(func, thisRef) {
            this.table.forEach(function(entry) {
                func.call(thisRef, entry.key)
            })
        };
        Dictionary.prototype.keys = function() {
            var keys = new Array();
            this.forEachKey(function(key) {
                keys.push(key)
            });
            return keys
        };
        MindFusion.registerClass(Dictionary, "MindFusion.Collections.Dictionary")
    })(MindFusion.Collections);
    (function(mcol) {
        var Grid = mcol.Grid = function(columns, rows) {
            this.clear();
            if (columns > 0 && rows > 0) {
                this.resize(columns, rows)
            }
        };
        Grid.prototype = {
            clone: function() {
                var copy = new Grid(this.columns, this.rows);
                for (var c = 0; c < this.columns; c++) {
                    for (var r = 0; r < this.rows; r++) {
                        var cell = this.get(c, r);
                        if (cell) {
                            if (cell.clone) {
                                copy.set(c, r, cell.clone())
                            } else {
                                copy.set(c, r, cell)
                            }
                        }
                    }
                }
                return copy
            },
            get: function(col, row) {
                return this.data[col][row]
            },
            set: function(col, row, value) {
                this.data[col][row] = value
            },
            clear: function() {
                this.data = [];
                this.columns = 0;
                this.rows = 0
            },
            resize: function(columns, rows) {
                this.columns = columns;
                this.rows = rows;
                this.data.length = columns;
                for (var i = 0; i < columns; i++) {
                    if (!this.data[i]) {
                        this.data[i] = []
                    }
                    this.data[i].length = rows
                }
            },
            deleteColumn: function(col) {
                this.data.splice(col, 1);
                this.columns--
            },
            insertColumn: function(col) {
                this.data.splice(col, 0, []);
                this.columns++;
                this.data[col].length = this.rows
            },
            deleteRow: function(row) {
                for (var i = 0; i < this.columns; i++) {
                    this.data[i].splice(row, 1)
                }
                this.rows--
            },
            insertRow: function(row) {
                for (var i = 0; i < this.columns; i++) {
                    this.data[i].splice(row, 0, null)
                }
                this.rows++
            }
        };
        MindFusion.registerClass(Grid, "MindFusion.Collections.Grid")
    })(MindFusion.Collections);
    (function(mcol) {
        var HashTable = mcol.HashTable = function() {
            this.buckets = new Array();
            this.size = 100;
            this.count = 0
        };
        HashTable.prototype.add = function(key) {
            this.count++;
            var bucket = this.bucket(key);
            var entry = {
                key: key
            };
            bucket.push(entry);
            return entry
        };
        HashTable.prototype.get = function(key) {
            var bucket = this.bucket(key);
            var index = this.indexInBucket(key, bucket);
            if (index == -1) {
                return null
            }
            return bucket[index]
        };
        HashTable.prototype.contains = function(key) {
            var entry = this.get(key);
            return entry != null
        };
        HashTable.prototype.remove = function(key) {
            var bucket = this.bucket(key);
            var index = this.indexInBucket(key, bucket);
            if (index == -1) {
                return null
            }
            this.count--;
            var entry = bucket[index];
            bucket.splice(index, 1);
            return entry
        };
        HashTable.prototype.forEach = function(func) {
            for (var bi = 0; bi < this.buckets.length; ++bi) {
                var bucket = this.buckets[bi];
                if (bucket == undefined) {
                    continue
                }
                for (var i = 0; i < bucket.length; ++i) {
                    func(bucket[i])
                }
            }
        };
        HashTable.prototype.bucket = function(key) {
            var index = this.hashCode(key) % this.size;
            var bucket = this.buckets[index];
            if (bucket === undefined) {
                bucket = new Array();
                this.buckets[index] = bucket
            }
            return bucket
        };
        HashTable.prototype.indexInBucket = function(key, bucket) {
            for (var i = 0; i < bucket.length; ++i) {
                var entry = bucket[i];
                if (entry.key === key) {
                    return i
                }
            }
            return -1
        };
        HashTable.prototype.hashCode = function(key) {
            if (typeof key == "number") {
                return key & key
            }
            if (typeof key == "string") {
                return this.hashString(key)
            }
            if (typeof key == "object") {
                return this.objectId(key)
            }
            throw new Error("Key type not supported.")
        };
        HashTable.prototype.hashString = function(s) {
            var hash = 0;
            if (s.length == 0) {
                return hash
            }
            for (var i = 0; i < s.length; i++) {
                var ch = s.charCodeAt(i);
                hash = ((hash << 5) - hash) + ch;
                hash = hash & hash
            }
            return Math.abs(hash)
        };
        HashTable.prototype.objectId = function(key) {
            var id = key._mf_autoId;
            if (id === undefined) {
                id = HashTable.objectIdCounter++;
                key._mf_autoId = id
            }
            return id
        };
        HashTable.objectIdCounter = 0;
        MindFusion.registerClass(HashTable, "MindFusion.Collections.HashTable")
    })(MindFusion.Collections);
    (function(mcol) {
        var ItemEventArgs = mcol.ItemEventArgs = function(item) {
            mflayer.initializeBase(ItemEventArgs, this);
            this._item = item
        };
        ItemEventArgs.prototype.get_item = function() {
            return this._item
        };
        MindFusion.registerClass(ItemEventArgs, "MindFusion.Collections.ItemEventArgs", MindFusion.EventArgs)
    })(MindFusion.Collections);
    (function(mcol) {
        var ArrayList = MindFusion.Collections.ArrayList;
        var ObservableCollection = mcol.ObservableCollection = function() {
            mflayer.initializeBase(ObservableCollection, this);
            this.eventHandlers = new mflayer.EventHandlerList()
        };
        ObservableCollection.prototype.add = function(item) {
            ArrayList.add(this, item);
            var handler = mflayer.getHandler(this, "itemAdded");
            var args = new MindFusion.Collections.ItemEventArgs(item);
            handler(this, args)
        };
        ObservableCollection.prototype.add_itemAdded = function(handler) {
            mflayer.addHandler(this, "itemAdded", handler)
        };
        ObservableCollection.prototype.remove_itemAdded = function(handler) {
            mflayer.removeHandler(this, "itemAdded", handler)
        };
        MindFusion.registerClass(ObservableCollection, "MindFusion.Collections.ObservableCollection", Array)
    })(MindFusion.Collections);
    (function(mcol) {
        var PriorityQueue = mcol.PriorityQueue = function(compareFunction) {
            this.heap = [null];
            this.size = 0;
            this.compareFunction = compareFunction
        };
        PriorityQueue.prototype = {
            add: function(v) {
                this.heap.push(null);
                this.heap[++this.size] = v;
                this.fixUp(this.size)
            },
            popMin: function() {
                this.swap(1, this.size);
                this.fixDown(1, this.size - 1);
                return this.heap[this.size--]
            },
            changePriority: function(v) {
                var k = this.heap.indexOf(v);
                this.fixUp(k);
                this.fixDown(k, this.size)
            },
            swap: function(i, j) {
                var t = this.heap[i];
                this.heap[i] = this.heap[j];
                this.heap[j] = t
            },
            fixUp: function(k) {
                while (k > 1 && this.more(Math.floor(k / 2), k)) {
                    this.swap(k, Math.floor(k / 2));
                    k = Math.floor(k / 2)
                }
            },
            fixDown: function(k, N) {
                while (2 * k <= N) {
                    var j = 2 * k;
                    if (j < N && this.more(j, j + 1)) {
                        j++
                    }
                    if (!this.more(k, j)) {
                        break
                    }
                    this.swap(k, j);
                    k = j
                }
            },
            empty: function() {
                return this.size == 0
            },
            more: function(i, j) {
                if (this.compareFunction) {
                    return this.compareFunction(this.heap[i], this.heap[j]) > 0
                }
                return this.heap[i] > this.heap[j]
            }
        };
        MindFusion.registerClass(PriorityQueue, "MindFusion.Collections.PriorityQueue")
    })(MindFusion.Collections);
    (function(mcol) {
        var Queue = mcol.Queue = function() {
            this.head = null;
            this.tail = null;
            this.size = 0
        };
        Queue.prototype.enqueue = function(value) {
            var entry = {
                value: value,
                next: null
            };
            if (this.head == null) {
                this.head = entry;
                this.tail = this.head
            } else {
                this.tail.next = entry;
                this.tail = this.tail.next
            }
            this.size++
        };
        Queue.prototype.dequeue = function() {
            if (this.size < 1) {
                throw new Error("Queue is empty.")
            }
            var value = this.head.value;
            this.head = this.head.next;
            this.size--;
            return value
        };
        Queue.prototype.getSize = function() {
            return this.size
        };
        MindFusion.registerClass(Queue, "MindFusion.Collections.Queue")
    })(MindFusion.Collections);
    (function(mcol) {
        var Set = mcol.Set = function() {
            this.table = new mcol.HashTable()
        };
        Set.prototype.add = function(key) {
            var entry = this.table.get(key);
            if (entry == null) {
                entry = this.table.add(key)
            }
        };
        Set.prototype.contains = function(key) {
            return this.table.contains(key)
        };
        Set.prototype.remove = function(key) {
            var entry = this.table.remove(key);
            if (entry) {
                return true
            }
            return false
        };
        Set.prototype.getCount = function() {
            return this.table.count
        };
        Set.prototype.forEach = function(func, thisRef) {
            this.table.forEach(function(entry) {
                func.call(thisRef, entry.key)
            })
        };
        MindFusion.registerClass(Set, "MindFusion.Collections.Set")
    })(MindFusion.Collections);
    MindFusion.Collections.Utilities = {
        indexOf: function(element) {
            for (var i = 0; i < this.length; ++i) {
                if (this[i] === element) {
                    return i
                }
            }
            return -1
        },
        remove: function(element) {
            var index = this.indexOf(element);
            if (index > -1) {
                this.splice(index, 1)
            }
        },
        contains: function(element) {
            return this.indexOf(element) > -1
        },
        any: function(predicate, thisRef) {
            for (var i = 0; i < this.length; ++i) {
                if (predicate.call(thisRef, this[i])) {
                    return this[i]
                }
            }
            return null
        },
        all: function(predicate, thisRef) {
            var result = [];
            for (var i = 0; i < this.length; ++i) {
                if (predicate.call(thisRef, this[i])) {
                    result.push(this[i])
                }
            }
            return result
        },
        forEach: function(func, thisRef) {
            for (var i = 0; i < this.length; ++i) {
                if (func.call(thisRef, this[i]) === $break) {
                    break
                }
            }
        },
        forReverse: function(func, thisRef) {
            for (var i = this.length - 1; i >= 0; i--) {
                if (func.call(thisRef, this[i]) === $break) {
                    break
                }
            }
        },
        mapTo: function(srcList, destList, func) {
            for (var i = 0, n = srcList.length; i < n; i++) {
                destList.push(func(srcList[i]))
            }
        }
    };
    MindFusion.registerNamespace("MindFusion.Geometry");
    (function(mgeo) {
        mgeo.cartesianToPolar = function(center, point) {
            if (center === point) {
                return {
                    a: 0,
                    r: 0
                }
            }
            var dx = point.x - center.x;
            var dy = point.y - center.y;
            var r = mgeo.distance(center, point);
            var a = Math.atan(-dy / dx);
            if (dx < 0) {
                a += Math.PI
            }
            return {
                a: a,
                r: r
            }
        };
        mgeo.cartesianToPolarDegrees = function(center, point) {
            var polar = mgeo.cartesianToPolar(center, point);
            polar.a = mgeo.radianToDegree(polar.a);
            return polar
        };
        mgeo.polarToCartesian = function(center, polar) {
            if (polar.r == 0) {
                return center
            }
            return {
                x: center.x + Math.cos(polar.a) * polar.r,
                y: center.y - Math.sin(polar.a) * polar.r
            }
        };
        mgeo.polarToCartesianDegrees = function(center, polar) {
            var polar2 = {
                a: mgeo.degreeToRadian(polar.a),
                r: polar.r
            };
            return mgeo.polarToCartesian(center, polar2)
        };
        mgeo.rotatePoint = function(point, pivot, angle) {
            var polar = mgeo.cartesianToPolar(pivot, point);
            polar.a += angle;
            return mgeo.polarToCartesian(pivot, polar)
        };
        mgeo.distance = function(p1, p2) {
            var dx = p1.x - p2.x;
            var dy = p1.y - p2.y;
            return Math.sqrt(dx * dx + dy * dy)
        };
        mgeo.radianToDegree = function(radian) {
            return radian * 180 / Math.PI
        };
        mgeo.degreeToRadian = function(degree) {
            return degree * Math.PI / 180
        };
        mgeo.getEllipseIntr = function(rcBox, pt1, pt2) {
            var pt = pt2.clone();
            var Rect = MindFusion.Drawing.Rect;
            var rc = Rect.fromLTRB(pt1.x, pt1.y, pt2.x, pt2.y);
            var x1 = pt1.x;
            var y1 = pt1.y;
            var x2 = pt2.x;
            var y2 = pt2.y;
            if (Math.abs(x1 - x2) > 0.0001) {
                var cx = (rcBox.left() + rcBox.right()) / 2;
                var cy = (rcBox.top() + rcBox.bottom()) / 2;
                var ea = (rcBox.right() - rcBox.left()) / 2;
                var eb = (rcBox.bottom() - rcBox.top()) / 2;
                var a = (y1 - y2) / (x1 - x2);
                var b = (x1 * y2 - x2 * y1) / (x1 - x2);
                var A = eb * eb + a * a * ea * ea;
                var B = 2 * a * (b - cy) * ea * ea - 2 * cx * eb * eb;
                var C = eb * eb * cx * cx + ea * ea * (b - cy) * (b - cy) - ea * ea * eb * eb;
                var X1, X2, Y1, Y2;
                var D = Math.sqrt(B * B - 4 * A * C);
                X1 = (-B + D) / (2 * A);
                X2 = (-B - D) / (2 * A);
                Y1 = a * X1 + b;
                Y2 = a * X2 + b;
                if (y1 == y2) {
                    Y1 = Y2 = y1
                }
                pt.x = X1;
                pt.y = Y1;
                if (pt.x >= rc.left() && pt.x <= rc.right() && pt.y >= rc.top() && pt.y <= rc.bottom()) {
                    return pt
                }
                pt.x = X2;
                pt.y = Y2;
                if (pt.x >= rc.left() && pt.x <= rc.right() && pt.y >= rc.top() && pt.y <= rc.bottom()) {
                    return pt
                }
            } else {
                var cx = (rcBox.left() + rcBox.right()) / 2;
                var cy = (rcBox.top() + rcBox.bottom()) / 2;
                var ea = (rcBox.right() - rcBox.left()) / 2;
                var eb = (rcBox.bottom() - rcBox.top()) / 2;
                var X = x1;
                var Y1 = cy - Math.sqrt((1 - (X - cx) * (X - cx) / (ea * ea)) * eb * eb);
                var Y2 = cy + Math.sqrt((1 - (X - cx) * (X - cx) / (ea * ea)) * eb * eb);
                pt.x = X;
                pt.y = Y1;
                if (pt.x >= rc.left() && pt.x <= rc.right() && pt.y >= rc.top() && pt.y <= rc.bottom()) {
                    return pt
                }
                pt.x = X;
                pt.y = Y2;
                if (pt.x >= rc.left() && pt.x <= rc.right() && pt.y >= rc.top() && pt.y <= rc.bottom()) {
                    return pt
                }
            }
            return pt
        }
    })(MindFusion.Geometry);
    (function(mgeo) {
        var ArrayList = MindFusion.Collections.ArrayList;
        var Circle = mgeo.Circle = function(x, y, r) {
            this.x = x;
            this.y = y;
            this.r = r
        };
        Circle.fromPoints = function(points) {
            if (points.length == 2) {
                return fromTwoPoints(points[0], points[1])
            }
            if (points.length == 3) {
                return fromThreePoints(points[0], points[1], points[2])
            }
            var p = {
                x: 0,
                y: Number.MAX_VALUE
            };
            ArrayList.forEach(points, function(point) {
                if (point.y < p.y) {
                    p = point
                }
            });
            var minAngle = Number.MAX_VALUE;
            var q = null;
            ArrayList.forEach(points, function(point) {
                if (p == point) {
                    return
                }
                var proj = {
                    x: point.x,
                    y: p.y
                };
                var angle = angleFromPoints(p, point, proj);
                if (angle < minAngle) {
                    minAngle = angle;
                    q = point
                }
            });
            var testedPoints = new MindFusion.Collections.Set();
            while (true) {
                minAngle = Number.MAX_VALUE;
                var r = null;
                ArrayList.forEach(points, function(point) {
                    if (p == point || q == point || testedPoints.contains(point)) {
                        return
                    }
                    var angle = angleFromPoints(point, p, q);
                    if (angle < minAngle) {
                        minAngle = angle;
                        r = point
                    }
                });
                var rAngle = minAngle;
                var pAngle = angleFromPoints(p, q, r);
                var qAngle = Math.PI - rAngle - pAngle;
                if (rAngle < Math.PI / 2 && pAngle < Math.PI / 2 && qAngle < Math.PI / 2) {
                    return Circle.fromThreePoints(p, q, r)
                }
                if (rAngle >= Math.PI / 2) {
                    return Circle.fromTwoPoints(p, q)
                }
                if (pAngle >= Math.PI / 2) {
                    testedPoints.add(p);
                    p = r
                } else {
                    if (qAngle >= Math.PI / 2) {
                        testedPoints.add(q);
                        q = r
                    } else {
                        return null
                    }
                }
            }
        };
        Circle.fromThreePoints = function(p1, p2, p3) {
            if (p1.x == p2.x) {
                var t = p3;
                p3 = p2;
                p2 = t
            } else {
                if (p3.x == p2.x) {
                    var t = p1;
                    p1 = p2;
                    p2 = t
                }
            }
            var x1 = p1.x;
            var y1 = p1.y;
            var x2 = p2.x;
            var y2 = p2.y;
            var x3 = p3.x;
            var y3 = p3.y;
            var ma = (y2 - y1) / (x2 - x1);
            var mb = (y3 - y2) / (x3 - x2);
            if (mb == ma) {
                return null
            }
            var x = (ma * mb * (y1 - y3) + mb * (x1 + x2) - ma * (x2 + x3)) / (2 * (mb - ma));
            var y = ma != 0 ? -(x - (x1 + x2) / 2) / ma + (y1 + y2) / 2 : -(x - (x2 + x3) / 2) / mb + (y2 + y3) / 2;
            var dx = x - x1;
            var dy = y - y1;
            var r = Math.sqrt(dx * dx + dy * dy);
            return new Circle(x, y, r)
        };
        Circle.fromTwoPoints = function(p1, p2) {
            var x = (p1.x + p2.x) / 2;
            var y = (p1.y + p2.y) / 2;
            var dx = x - p1.x;
            var dy = y - p1.y;
            var r = Math.sqrt(dx * dx + dy * dy);
            return new Circle(x, y, r)
        };

        function angleFromPoints(p1, p2, p3) {
            if (p2.x == p3.x && p2.y == p3.y) {
                return 0
            }
            var d12 = mgeo.distance(p1, p2);
            var d13 = mgeo.distance(p1, p3);
            var d23 = mgeo.distance(p2, p3);
            return Math.acos((d12 * d12 + d13 * d13 - d23 * d23) / (2 * d12 * d13))
        }
        mgeo.distance = function(p1, p2) {
            var dx = p1.x - p2.x;
            var dy = p1.y - p2.y;
            return Math.sqrt(dx * dx + dy * dy)
        };
        MindFusion.registerClass(Circle, "MindFusion.Geometry.Circle")
    })(MindFusion.Geometry);
    MindFusion.registerNamespace("MindFusion.Drawing");
    (function(mdraw) {
        mdraw.Visibility = {
            Hidden: 0,
            Collapsed: 1,
            Visible: 2
        };
        mdraw.LayoutAlignment = {
            Near: 0,
            Center: 1,
            Far: 2,
            Stretch: 3
        };
        mdraw.DashStyle = {
            Solid: 0,
            Dash: 1,
            Dot: 2,
            DashDot: 3,
            DashDotDot: 4,
            Custom: 5,
            apply: function(context, dashStyle) {
                if (!context.setLineDash) {
                    return
                }
                var scale = 2 / context._mf_scale;
                if (dashStyle == 1) {
                    context.setLineDash([4 * scale, scale])
                } else {
                    if (dashStyle == 2) {
                        context.setLineDash([scale, scale])
                    } else {
                        if (dashStyle == 3) {
                            context.setLineDash([4 * scale, scale, scale, scale])
                        } else {
                            if (dashStyle == 4) {
                                context.setLineDash([4 * scale, scale, scale, scale, scale, scale])
                            } else {
                                context.setLineDash([])
                            }
                        }
                    }
                }
            }
        };
        mdraw.ImageAlign = {
            Center: 0,
            Fit: 1,
            Stretch: 2,
            Tile: 3,
            TopLeft: 4,
            BottomLeft: 5,
            TopRight: 6,
            BottomRight: 7,
            TopCenter: 8,
            BottomCenter: 9,
            MiddleLeft: 10,
            MiddleRight: 11
        };
        mdraw.FontStyle = {
            Regular: 0,
            Bold: 1,
            Italic: 2,
            Underline: 4
        }
    })(MindFusion.Drawing);
    (function(mdraw) {
        var Thickness = mdraw.Thickness = function(left, top, right, bottom) {
            this.left = left;
            this.top = top;
            this.right = right;
            this.bottom = bottom
        };
        Thickness.prototype = {
            applyTo: function(rect) {
                rect.x += this.left;
                rect.y += this.top;
                rect.width -= this.width();
                if (rect.width < 0) {
                    rect.width = 0
                }
                rect.height -= this.height();
                if (rect.height < 0) {
                    rect.height = 0
                }
            },
            addToRect: function(rect) {
                rect.x -= this.left;
                rect.y -= this.top;
                rect.width += this.width();
                rect.height += this.height()
            },
            width: function() {
                return this.right + this.left
            },
            height: function() {
                return this.bottom + this.top
            }
        };
        Thickness.copy = function(size) {
            return new Thickness(size.left, size.top, size.right, size.bottom)
        };
        MindFusion.registerClass(Thickness, "MindFusion.Drawing.Thickness")
    })(MindFusion.Drawing);
    (function(mdraw) {
        var Size = mdraw.Size = function(width, height) {
            this.width = width;
            this.height = height
        };
        Size.prototype = {
            empty: function() {
                return (this.width === 0 && this.height === 0)
            }
        };
        Size.copy = function(size) {
            return new Size(size.width, size.height)
        };
        MindFusion.registerClass(Size, "MindFusion.Drawing.Size")
    })(MindFusion.Drawing);
    (function(mdraw) {
        var Arc = mdraw.Arc = function(x, y, radius, startAngle, endAngle, anticlockwise) {
            this.x = x;
            this.y = y;
            this.radius = radius;
            this.startAngle = startAngle;
            this.endAngle = endAngle;
            this.anticlockwise = anticlockwise;
            this.center = new mdraw.Point(x, y)
        };
        Arc.prototype = {
            getType: function() {
                return this.constructor.__typeName
            },
            draw: function(context) {
                context.strokeStyle = this.pen;
                context.lineWidth = (this.strokeThickness ? this.strokeThickness : 1) / context._mf_scale;
                mdraw.DashStyle.apply(context, this.strokeDashStyle);
                context.beginPath();
                context.arc(this.x, this.y, this.radius, this.startAngle, this.endAngle, this.anticlockwise);
                context.stroke()
            },
            containsPoint: function(point) {
                var d = mdraw.Point.distance(point, this.center);
                return (d <= this.radius)
            },
            inflate: function(halfIncrement) {
                if (!halfIncrement) {
                    return this
                }
                var newArc = new Arc(this.x, this.y, this.radius + halfIncrement, this.startAngle, this.endAngle, this.anticlockwise);
                return newArc
            },
            createSvgElement: function(svgdoc) {
                var isCircle = (this.endAngle - this.startAngle == 2 * Math.PI);
                var r = this.radius;
                var sx = this.x + r * Math.cos(this.startAngle);
                var sy = this.y + r * Math.sin(this.startAngle);
                var ex = this.x + r * Math.cos(this.endAngle);
                var ey = this.y + r * Math.sin(this.endAngle);
                var lasf = 0;
                var sf = 0;
                if (!isCircle) {
                    if (this.anticlockwise && this.endAngle - this.startAngle < Math.PI) {
                        lasf = 1;
                        sf = 0
                    }
                    if (this.anticlockwise && this.endAngle - this.startAngle >= Math.PI) {
                        lasf = sf = 0
                    }
                    if (!this.anticlockwise && this.endAngle - this.startAngle < Math.PI) {
                        lasf = 0;
                        sf = 1
                    }
                    if (!this.anticlockwise && this.endAngle - this.startAngle >= Math.PI) {
                        lasf = sf = 1
                    }
                    var arcPath = svgdoc.createElementNS("http://www.w3.org/2000/svg", "path");
                    var value = "A" + r + ", " + r + ", 0, " + lasf + ", " + sf + ", " + ex + ", " + ey;
                    arcPath.setAttribute("d", value);
                    return arcPath
                } else {
                    var mx = this.x + r * Math.cos(this.startAngle + Math.PI);
                    var my = this.y + r * Math.sin(this.startAngle + Math.PI);
                    var arcPath = svgdoc.createElementNS("http://www.w3.org/2000/svg", "path");
                    sf = this.anticlockwise ? 0 : 1;
                    var value = "A" + r + ", " + r + ", 0, " + lasf + ", " + sf + ", " + mx + ", " + my + " A" + r + ", " + r + ", 0, " + lasf + ", " + sf + ", " + ex + ", " + ey;
                    arcPath.setAttribute("d", value);
                    return arcPath
                }
            },
            pen: "black"
        };
        MindFusion.registerClass(Arc, "MindFusion.Drawing.Arc")
    })(MindFusion.Drawing);
    (function(mdraw) {
        var Bezier = mdraw.Bezier = function(x1, y1, x2, y2, x3, y3, x4, y4) {
            this.x1 = x1;
            this.y1 = y1;
            this.x2 = x2;
            this.y2 = y2;
            this.x3 = x3;
            this.y3 = y3;
            this.x4 = x4;
            this.y4 = y4
        };
        Bezier.fromPoints = function(points, start) {
            return new Bezier(points[start + 0].x, points[start + 0].y, points[start + 1].x, points[start + 1].y, points[start + 2].x, points[start + 2].y, points[start + 3].x, points[start + 3].y)
        };
        Bezier.prototype = {
            pen: "black",
            strokeThickness: 0,
            draw: function(context, drawShadow) {
                if (this.shadow && drawShadow != false) {
                    context.save();
                    this.shadow.apply(context)
                }
                context.strokeStyle = this.pen;
                context.lineWidth = (this.strokeThickness ? this.strokeThickness : 1) / context._mf_scale;
                mdraw.DashStyle.apply(context, this.strokeDashStyle);
                context.beginPath();
                context.moveTo(this.x1, this.y1);
                context.bezierCurveTo(this.x2, this.y2, this.x3, this.y3, this.x4, this.y4);
                context.stroke();
                if (this.shadow && drawShadow != false) {
                    context.restore()
                }
            },
            drawShadow: function(context) {
                if (this.shadow) {
                    context.save();
                    this.shadow.apply(context);
                    context.lineWidth = (this.strokeThickness ? this.strokeThickness : 1) / context._mf_scale;
                    context.beginPath();
                    context.moveTo(this.x1, this.y1);
                    context.bezierCurveTo(this.x2, this.y2, this.x3, this.y3, this.x4, this.y4);
                    context.strokeStyle = this.shadow.color;
                    context.stroke();
                    context.restore()
                }
            },
            addToContext: function(context) {
                context.beginPath();
                context.moveTo(this.x1, this.y1);
                context.bezierCurveTo(this.x2, this.y2, this.x3, this.y3, this.x4, this.y4)
            },
            createSvgElement: function(svgdoc) {
                var element = svgdoc.createElementNS("http://www.w3.org/2000/svg", "path");
                var value = "M" + this.x1 + "," + this.y1;
                value += " C" + this.x2 + "," + this.y2 + "," + this.x3 + "," + this.y3 + "," + this.x4 + "," + this.y4;
                element.setAttribute("d", value);
                element.setAttribute("fill", "none");
                element.setAttribute("stroke", "black");
                element.setAttribute("stroke-width", this.strokeThickness ? this.strokeThickness / 4 : 1 / 4);
                return element
            }
        };
        MindFusion.registerClass(Bezier, "MindFusion.Drawing.Bezier")
    })(MindFusion.Drawing);
    (function(mdraw) {
        var Border3D = mdraw.Border3D = function(rect) {
            this.rect = rect;
            this.transform = new mdraw.Matrix();
            this.type = this.constructor.__typeName
        };
        Border3D.prototype = {
            getType: function() {
                return this.type
            },
            draw: function(context, drawShadow) {
                function drawLine(x1, y1, x2, y2) {
                    function alignToPixel(val) {
                        var aligned = Math.round(val * context._mf_scale) / context._mf_scale;
                        return aligned
                    }
                    context.beginPath();
                    context.moveTo(alignToPixel(x1), alignToPixel(y1));
                    context.lineTo(alignToPixel(x2), alignToPixel(y2));
                    context.stroke()
                }
                var bounds = this.rect;
                var pixel = 1 / context._mf_scale;
                var px2 = pixel * 2;
                context.save();
                context.lineWidth = pixel;
                context.strokeStyle = "gray";
                drawLine(bounds.left(), bounds.top(), bounds.right(), bounds.top());
                drawLine(bounds.left(), bounds.top(), bounds.left(), bounds.bottom());
                context.strokeStyle = "darkGray";
                drawLine(bounds.left() + pixel, bounds.top() + pixel, bounds.right() - 2 * pixel, bounds.top() + pixel);
                drawLine(bounds.left() + pixel, bounds.top() + pixel, bounds.left() + pixel, bounds.bottom() - 2 * pixel);
                context.strokeStyle = "white";
                drawLine(bounds.left(), bounds.bottom() - pixel, bounds.right(), bounds.bottom() - pixel);
                drawLine(bounds.left() + pixel, bounds.bottom() - px2, bounds.right() - pixel, bounds.bottom() - px2);
                drawLine(bounds.right() - pixel, bounds.top(), bounds.right() - pixel, bounds.bottom());
                drawLine(bounds.right() - px2, bounds.top() + pixel, bounds.right() - px2, bounds.bottom() - pixel);
                context.restore()
            },
            createSvgElement: function(svgdoc) {
                return this.rect.createSvgElement(svgdoc)
            }
        };
        MindFusion.registerClass(Border3D, "MindFusion.Drawing.Border3D")
    })(MindFusion.Drawing);
    (function(mdraw) {
        var ArrayList = MindFusion.Collections.ArrayList;
        var dec = String.fromCharCode;
        var Canvas = mdraw.Canvas = function(element) {
            if (!start) {
                start = new Date().getTime()
            }
            mflayer.initializeBase(Canvas, this, [element]);
            this.licenseLocation = "";
            this.elements = [];
            this.font = new mdraw.Font("sans-serif", 4);
            this.measureUnit = mdraw.GraphicsUnit.Millimeter;
            this.zoomFactor = 100;
            this.bounds = new mdraw.Rect(0, 0, 210, 297);
            this.scrollbarSize = 16;
            this.repaintDelegate = mflayer.createDelegate(this, this.repaint)
        };
        Canvas.prototype = {
            initialize: function() {
                this.suppressPaint = true;
                mflayer.callBaseMethod(Canvas, this, "initialize");
                var canvas = this.get_element();
                if (typeof canvas.getContext !== "undefined") {
                    this.context = canvas.getContext("2d")
                }
                this.updateScale();
                this.suppressPaint = false
            },
            dispose: function() {
                mflayer.callBaseMethod(Canvas, this, "dispose")
            },
            setBounds: function(bounds) {
                this.bounds = bounds;
                this.updateCanvasSize()
            },
            getBounds: function() {
                return this.bounds
            },
            setMeasureUnit: function(value) {
                if (this.measureUnit !== value) {
                    this.measureUnit = value;
                    this.updateScale()
                }
            },
            getMeasureUnit: function() {
                return this.measureUnit
            },
            setZoomFactor: function(value) {
                if (this.zoomFactor !== value) {
                    if (this.getScrollX) {
                        var dx = this.getScrollX();
                        var dy = this.getScrollY();
                        this.zoomFactor = value;
                        this.updateScale();
                        this.setScrollX(dx);
                        this.setScrollY(dy);
                        this.raiseEvent("zoomChanged", null)
                    } else {
                        this.zoomFactor = value;
                        this.updateScale();
                        this.raiseEvent("zoomChanged", null)
                    }
                }
            },
            setZoomFactorAndScroll: function(value, sx, sy) {
                this.zoomFactor = value;
                this.scale = this.zoomFactor / 100 / mdraw.GraphicsUnit.getPixel(this.measureUnit);
                this.setScrollX(sx);
                this.setScrollY(sy);
                this.updateScale();
                this.raiseEvent("zoomChanged", null)
            },
            getZoomFactor: function() {
                return this.zoomFactor
            },
            getBackgroundImage: function() {
                return null
            },
            getBackgroundImageSize: function() {
                return new MindFusion.Drawing.Size(0, 0)
            },
            getBackgroundImageAlign: function() {
                return MindFusion.Drawing.ImageAlign.Center
            },
            getLicenseLocation: function() {
                return this.licenseLocation
            },
            setLicenseLocation: function(value) {
                if (this.licenseLocation != value) {
                    this.licenseLocation = value
                }
            },
            repaint: function() {
                if (!this.context) {
                    return
                }
                var matrix = new MindFusion.Drawing.Matrix();
                if (this.scroller) {
                    matrix.translate(-this.scroller.scrollLeft, -this.scroller.scrollTop)
                }
                matrix.scale(this.scale, this.scale);
                matrix.translate(-this.bounds.x, -this.bounds.y);
                this.context._mf_transform = matrix;
                this.context._mf_scale = this.scale;
                this.context._mf_minVisibleFontSize = this.minVisibleFontSize;
                this.context._mf_measureUnit = this.measureUnit;
                this.drawBackground();
                this.context.save();
                this.context.transform.apply(this.context, matrix.matrix());
                if (this.showLaneGrid) {
                    this.laneGrid.drawFirst(this.context)
                }
                this.context.lineWidth = 1 / this.scale;
                if (this.showGrid) {
                    this.drawGrid()
                }
                if (this.updateLabelLayout) {
                    this.updateLabelLayout(this.bounds)
                }
                var drawShadows = (this.shadowsStyle == MindFusion.Diagramming.ShadowsStyle.ZOrder);
                if (this.shadowsStyle == MindFusion.Diagramming.ShadowsStyle.OneLevel) {
                    this.drawShadows()
                }
                var zOrder = this.getZOrder();
                for (var i = 0; i < zOrder.length; i++) {
                    var element = zOrder[i];
                    if (element.invisible || (element.item && element.item.getTopLevel() == false)) {
                        continue
                    }
                    element.draw(this.context, drawShadows, false)
                }
                if (this.showLaneGrid) {
                    this.laneGrid.drawLast(this.context)
                }
                if (this.drawForeground) {
                    this.drawForeground()
                }
                if (this.mouseInputDispatcher && this.mouseInputDispatcher.currentController) {
                    this.mouseInputDispatcher.currentController.drawInteraction(this.context)
                }
                if (this.magnifierEnabled && this.drawMagnifier) {
                    this.drawMagnifier()
                }
                if (this.raiseEvent) {
                    this.raiseEvent("repaint", MindFusion.EventArgs.Empty)
                }
                this.context.restore();
                drawMessage(this);
                if (this.repaintId) {
                    clearTimeout(this.repaintId);
                    this.repaintId = null
                }
            },
            drawBackground: function() {
                var b = this.bounds;
                var sb = this.context._mf_transform.transformRect(b);
                this.context.save();
                this.context.clearRect(sb.x, sb.y, sb.width, sb.height);
                this.context.beginPath();
                this.context.rect(sb.x, sb.y, sb.width, sb.height);
                this.context.fillStyle = MindFusion.Diagramming.Utils.getBrush(this.context, this.getEffectiveBackBrush != null ? this.getEffectiveBackBrush() : this.backBrush, sb);
                this.context.fill();
                this.context.restore();
                var img = this.getBackgroundImage();
                var imgSize = this.getBackgroundImageSize();
                var align = this.getBackgroundImageAlign();
                if (img && img.loaded) {
                    var a = MindFusion.Drawing.ImageAlign;
                    switch (align) {
                        case a.Center:
                            this.context.drawImage(img, (sb.right() + sb.x - imgSize.width) / 2, (sb.bottom() + sb.y - imgSize.height) / 2, imgSize.width, imgSize.height);
                            break;
                        case a.Fit:
                            var rb = sb.width / sb.height;
                            var ri = imgSize.width / imgSize.height;
                            if (rb > ri) {
                                this.context.drawImage(img, (sb.right() + sb.x - imgSize.width * ri) / 2, sb.y, ri * imgSize.height, sb.height)
                            } else {
                                this.context.drawImage(img, sb.x, (sb.bottom() + sb.y - imgSize.width / ri) / 2, sb.width, ri * imgSize.width)
                            }
                            break;
                        case a.Stretch:
                            this.context.drawImage(img, sb.x, sb.y, sb.width, sb.height);
                            break;
                        case a.Tile:
                            for (var w = 0; w < sb.width; w += imgSize.width) {
                                for (var h = 0; h < sb.height; h += imgSize.height) {
                                    this.context.drawImage(img, w, h, imgSize.width, imgSize.height)
                                }
                            }
                            break;
                        case a.TopLeft:
                            this.context.drawImage(img, sb.x, sb.y, imgSize.width, imgSize.height);
                            break;
                        case a.BottomLeft:
                            this.context.drawImage(img, sb.x, sb.bottom() - imgSize.height, imgSize.width, imgSize.height);
                            break;
                        case a.TopRight:
                            this.context.drawImage(img, sb.right() - imgSize.width, sb.y, imgSize.width, imgSize.height);
                            break;
                        case a.BottomRight:
                            this.context.drawImage(img, sb.right() - imgSize.width, sb.bottom() - imgSize.height, imgSize.width, imgSize.height);
                            break;
                        case a.TopCenter:
                            this.context.drawImage(img, sb.x + sb.width / 2 - imgSize.width / 2, sb.y, imgSize.width, imgSize.height);
                            break;
                        case a.BottomCenter:
                            this.context.drawImage(img, sb.x + sb.width / 2 - imgSize.width / 2, sb.bottom() - imgSize.height, imgSize.width, imgSize.height);
                            break;
                        case a.MiddleLeft:
                            this.context.drawImage(img, sb.x, sb.y + sb.height / 2 - imgSize.height / 2, imgSize.width, imgSize.height);
                            break;
                        case a.MiddleRight:
                            this.context.drawImage(img, sb.right() - imgSize.width, sb.y + sb.height / 2 - imgSize.height / 2, imgSize.width, imgSize.height);
                            break
                    }
                }
            },
            drawShadows: function() {
                var zOrder = this.getZOrder();
                for (var i = 0; i < zOrder.length; i++) {
                    var element = zOrder[i];
                    if (element.invisible || (element.item && element.item.getTopLevel() == false)) {
                        continue
                    }
                    element.draw(this.context, true, true)
                }
            },
            clientToDoc: function(point) {
                var x = point.x;
                var y = point.y;
                if (this.scroller) {
                    x += this.scroller.scrollLeft;
                    y += this.scroller.scrollTop
                }
                var p = new mdraw.Point(x / this.scale, y / this.scale);
                return new mdraw.Point(p.x + this.bounds.x, p.y + this.bounds.y)
            },
            clientToDocLength: function(length) {
                return length / this.scale
            },
            clientToDocOverflow: function(point) {
                var p = new mdraw.Point(point.x / this.scale, point.y / this.scale);
                return new mdraw.Point(p.x + this.bounds.x, p.y + this.bounds.y)
            },
            docToClient: function(point) {
                var p = new mdraw.Point(point.x - this.bounds.x, point.y - this.bounds.y);
                p = new mdraw.Point(p.x * this.scale, p.y * this.scale);
                if (this.scroller) {
                    p.x -= this.scroller.scrollLeft;
                    p.y -= this.scroller.scrollTop
                }
                return p
            },
            docToClientOverflow: function(point) {
                var p = new mdraw.Point(point.x - this.bounds.x, point.y - this.bounds.y);
                return new mdraw.Point(p.x * this.scale, p.y * this.scale)
            },
            measureString: function(text, font, bounds, styled) {
                if (!this.context) {
                    return new mdraw.Rect(0, 0, 20, 10)
                }
                if (!bounds) {
                    bounds = new mdraw.Rect(0, 0, Number.MAX_VALUE, Number.MAX_VALUE)
                }
                var t = new mdraw.Text(text, bounds);
                t.font = font;
                t.padding = new mdraw.Thickness(0, 0, 0, 0);
                t.enableStyledText = styled;
                this.context.save();
                this.context.font = font.toString();
                var size;
                if (styled) {
                    size = t.measureStyledText(this.context, bounds.width)
                } else {
                    this.context.scale(this.scale, this.scale);
                    var lines = t.getLines(this.context, bounds);
                    if (bounds.width != Number.MAX_VALUE) {
                        var width = bounds.width
                    } else {
                        var width = this.context.measureText(text).width;
                        if (lines.length > 1) {
                            var lw = 0;
                            for (var i = 0; i < lines.length; i++) {
                                lw = Math.max(lw, this.context.measureText(lines[i]).width)
                            }
                            width = lw
                        }
                    }
                    var height = ((t.font.size) * lines.length);
                    size = new mdraw.Size(width, height)
                }
                this.context.restore();
                return size
            },
            getRectIntersection: function(rect, point1, point2, pt) {
                var rc = new mdraw.Rect.fromLTRB(point1.x, point1.y, point2.x, point2.y);
                rc = MindFusion.Diagramming.Utils.normalizeRect(rc);
                var x1 = point1.x;
                var y1 = point1.y;
                var x2 = point2.x;
                var y2 = point2.y;
                if (x1 === x2) {
                    pt.x = x1;
                    pt.y = rect.y;
                    if (pt.x >= rect.x && pt.x <= rect.right() && pt.y >= rc.y && pt.y <= rc.bottom()) {
                        return
                    }
                    pt.y = rect.bottom();
                    if (pt.x >= rect.x && pt.x <= rect.right() && pt.y >= rc.y && pt.y <= rc.bottom()) {
                        return
                    }
                } else {
                    if (y1 === y2) {
                        pt.y = y1;
                        pt.x = rect.x;
                        if (pt.y >= rect.y && pt.y <= rect.bottom() && pt.x >= rc.x && pt.x <= rc.right()) {
                            return
                        }
                        pt.x = rect.right();
                        if (pt.y >= rect.y && pt.y <= rect.bottom() && pt.x >= rc.x && pt.x <= rc.right()) {
                            return
                        }
                    } else {
                        var a = (y1 - y2) / (x1 - x2);
                        var b = (x1 * y2 - x2 * y1) / (x1 - x2);
                        pt.y = rect.y;
                        pt.x = (pt.y - b) / a;
                        if (pt.x >= rect.x && pt.x <= rect.right() && pt.y <= rect.bottom() && pt.y >= rc.y && pt.y <= rc.bottom()) {
                            return
                        }
                        pt.y = rect.bottom();
                        pt.x = (pt.y - b) / a;
                        if (pt.x >= rect.x && pt.x <= rect.right() && pt.y >= rect.y && pt.y >= rc.y && pt.y <= rc.bottom()) {
                            return
                        }
                        pt.x = rect.x;
                        pt.y = a * pt.x + b;
                        if (pt.y >= rect.y && pt.y <= rect.bottom() && pt.x <= rect.right() && pt.x >= rc.x && pt.x <= rc.right()) {
                            return
                        }
                        pt.x = rect.right();
                        pt.y = a * pt.x + b;
                        if (pt.y >= rect.y && pt.y <= rect.bottom() && pt.x >= rect.x && pt.x >= rc.x && pt.x <= rc.right()) {
                            return
                        }
                    }
                }
            },
            addElement: function(element) {
                this.elements.push(element);
                if (this.cachedZOrder && (element.zIndex === Number.MAX_VALUE || element.zIndex === undefined)) {
                    this.cachedZOrder.push(element)
                } else {
                    this.cachedZOrder = null
                }
                this.invalidate()
            },
            removeElement: function(element) {
                ArrayList.remove(this.elements, element);
                if (this.cachedZOrder) {
                    ArrayList.remove(this.cachedZOrder, element)
                }
                this.invalidate()
            },
            invalidate: function() {
                if (!this.repaintId) {
                    this.repaintId = setTimeout(this.repaintDelegate, 20)
                }
            },
            invalidateZOrder: function() {
                this.cachedZOrder = null;
                this.invalidate()
            },
            getZOrder: function() {
                if (!this.cachedZOrder) {
                    this.cachedZOrder = ArrayList.clone(this.elements);
                    this.cachedZOrder.sort(compareZ)
                }
                if (this.updateContainersZOrder) {
                    this.updateContainersZOrder()
                }
                return this.cachedZOrder
            },
            updateScale: function() {
                this.scale = this.zoomFactor / 100 / mdraw.GraphicsUnit.getPixel(this.measureUnit);
                if (this.context) {
                    this.context._mf_scale = this.scale
                }
                this.updateCanvasSize()
            },
            updateCanvasSize: function() {
                if (this.scroller) {
                    var parentDiv = this.get_element().parentNode;
                    this.innerScroller.style.width = this.bounds.width * this.scale + "px";
                    this.innerScroller.style.height = this.bounds.height * this.scale + "px";
                    var bounds = mflayer.getBounds(parentDiv);
                    var h = bounds.height;
                    if (h == 0) {
                        h = parentDiv._mf_originalHeight
                    }
                    if (h == 0) {
                        h = this.bounds.height * this.scale
                    }
                    this.get_element().width = Math.min(bounds.width - this.getScrollbarSize(1), this.bounds.width * this.scale);
                    this.get_element().height = Math.min(h - this.getScrollbarSize(0), this.bounds.height * this.scale)
                } else {
                    this.get_element().width = this.bounds.width * this.scale;
                    this.get_element().height = this.bounds.height * this.scale
                }
                this.repaint();
                if (this.raiseEvent) {
                    this.raiseEvent("sizeChanged", MindFusion.EventArgs.Empty)
                }
            },
            sizeElement: function() {
                return this.innerScroller ? this.innerScroller : this.get_element()
            },
            scrollElement: function() {
                return this.scroller ? this.scroller : this.get_element().parentNode
            },
            getScrollbarSize: function(type) {
                if (!this.scroller) {
                    return 0
                }
                if (type == 0 && this.scroller.scrollWidth > this.scroller.clientWidth) {
                    return this.scrollbarSize
                }
                if (type == 1 && this.scroller.scrollHeight > this.scroller.clientHeight) {
                    return this.scrollbarSize
                }
                return 0
            },
            drawGrid: function() {
                var pointSize = this.getGridPointSize();
                if (!this.showGrid) {
                    return
                }
                var strokeStyle = this.context.strokeStyle;
                this.context.strokeStyle = this.gridColor;
                var nx = (this.bounds.width - this.gridOffsetX) / this.gridSizeX;
                var ny = (this.bounds.height - this.gridOffsetY) / this.gridSizeY;
                var pixelSize = mdraw.GraphicsUnit.getPixel(this.measureUnit);
                var gridSizeXInPixels = this.gridSizeX / pixelSize;
                var gridSizeYInPixels = this.gridSizeY / pixelSize;
                var scrLeft = this.get_element().parentNode.scrollLeft;
                var scrTop = this.get_element().parentNode.scrollTop;
                var diagClientW = this.get_element().parentNode.offsetWidth;
                var diagClientH = this.get_element().parentNode.offsetHeight;
                var viewport = this.getViewport();
                var topLeft = viewport.topLeft();
                var bottomRight = viewport.bottomRight();
                var lineX = Math.floor((topLeft.x - this.gridOffsetX) / this.gridSizeX);
                var lineY = Math.floor((topLeft.y - this.gridOffsetY) / this.gridSizeY);
                this.context.beginPath();
                if (this.gridStyle == MindFusion.Diagramming.GridStyle.Points) {
                    for (var i = 0; i <= ny; ++i) {
                        var y = (i + lineY) * this.gridSizeY + this.gridOffsetY;
                        if (y > bottomRight.y) {
                            break
                        }
                        for (var j = 0; j <= nx; ++j) {
                            var x = (j + lineX) * this.gridSizeX + this.gridOffsetX;
                            if (x > bottomRight.x) {
                                break
                            }
                            this.drawDashLine(x, y, x + pixelSize, y + pixelSize)
                        }
                    }
                } else {
                    if (this.gridStyle == MindFusion.Diagramming.GridStyle.Lines) {
                        for (var i = 1; i <= ny; ++i) {
                            var y = (i + lineY) * this.gridSizeY + this.gridOffsetY;
                            if (y > bottomRight.y) {
                                break
                            }
                            this.drawDashLine(this.bounds.x, y, this.bounds.right(), y)
                        }
                        for (var j = 1; j <= nx; ++j) {
                            var x = (j + lineX) * this.gridSizeX + this.gridOffsetX;
                            if (x > bottomRight.x) {
                                break
                            }
                            this.drawDashLine(x, this.bounds.y, x, this.bounds.bottom())
                        }
                    } else {
                        if (this.gridStyle == MindFusion.Diagramming.GridStyle.Crosses) {
                            var pointOffset = this.gridPointSize / 2;
                            for (var i = 0; i <= ny; ++i) {
                                var y = (i + lineY) * this.gridSizeY + this.gridOffsetY;
                                if (y > bottomRight.y) {
                                    break
                                }
                                for (var j = 0; j <= nx; ++j) {
                                    var x = (j + lineX) * this.gridSizeX + this.gridOffsetX;
                                    if (x > bottomRight.x) {
                                        break
                                    }
                                    this.drawDashLine(x - pointOffset, y, x + pointOffset, y);
                                    this.drawDashLine(x, y - pointOffset, x, y + pointOffset)
                                }
                            }
                        }
                    }
                }
                this.context.stroke();
                this.context.strokeStyle = strokeStyle
            },
            drawDashLine: function(fromX, fromY, toX, toY) {
                this.context.moveTo(fromX, fromY);
                this.context.lineTo(toX, toY)
            },
            onLoad: function() {
                d = null
            },
            setLicenseKey: function(value) {
                licenseKey = value
            },
            setMinVisibleFontSize: function(value) {
                this.minVisibleFontSize = value
            },
            getMinVisibleFontSize: function() {
                return this.minVisibleFontSize
            },
            minVisibleFontSize: 0
        };

        function compareZ(e1, e2) {
            var i1 = e1.zIndex;
            if (i1 === undefined) {
                i1 = Number.MAX_VALUE
            }
            var i2 = e2.zIndex;
            if (i2 === undefined) {
                i2 = Number.MAX_VALUE
            }
            if (i1 < i2) {
                return -1
            }
            if (i1 > i2) {
                return 1
            }
            return 0
        }
        var start, startXhr;

        function drawMessage(canvas) {
            if (!d && new Date().getTime() - start > 8000) {
                d = MindFusion.Diagramming.Diagram
            }
            if (!d) {
                return
            }
            if (canvas.req != undefined) {
                req = canvas.req
            }
            if (req(canvas)) {
                return
            }
            dm = dec.apply(undefined, mdraw.Gradient.tm);
            ns = dm == 0 ? "" : d.ns;
            var bounds = new mdraw.Rect(10, 10, canvas.bounds.width, canvas.bounds.height);
            var text = new mdraw.Text(ns + dm, bounds);
            text.pen = "#FC1010";
            text.font = new mdraw.Font("sans-serif", 12);
            text.ignoreTransform = true;
            text.draw(canvas.context)
        }
        var d = null,
            dm = null,
            ns;
        var rd = new Date(2017, 11, 8);
        var f, dt, checked = false,
            nowait = false;
        var licenseKey = null;
        var logMeInConsole = function() {
            return String.fromCharCode.apply(undefined, [77, 105, 110, 100, 70, 117, 115, 105, 111, 110, 46, 68, 105, 97, 103, 114, 97, 109, 109, 105, 110, 103, 32, 108, 105, 99, 101, 110, 115, 101, 100, 32, 116, 111, 32])
        };
        var ab2str = function(buf) {
            return String.fromCharCode.apply(null, new Uint16Array(buf))
        };
        var str2ab = function(str) {
            var buf = new ArrayBuffer(str.length * 2);
            var bufView = new Uint16Array(buf);
            for (var i = 0, strLen = str.length; i < strLen; i++) {
                bufView[i] = str.charCodeAt(i)
            }
            return buf
        };

        function req(canvas) {
            var lc = function(content) {
                var cb = new Uint16Array(str2ab(decodeURIComponent(atob(content)))),
                    cc_1 = new Uint16Array(cb.length);
                for (var i = 0; i < cb.length; i++) {
                    var v = cb[i];
                    cc_1[i] = ~(v ^ ~"73") ^ 42
                }
                var strl = ab2str(cc_1.buffer);
                var flag1 = false,
                    flag2 = false;
                var a1 = "",
                    a2 = "";
                for (var strli = 0; strli < strl.length; strli++) {
                    if (!flag1) {
                        if (strl[strli] == ".") {
                            flag1 = true;
                            continue
                        }
                        a1 += strl[strli];
                        continue
                    }
                    if (!flag2) {
                        if (strl[strli] == ".") {
                            flag2 = true;
                            continue
                        }
                        a2 += strl[strli];
                        continue
                    }
                    break
                }
                var c1 = "",
                    c2 = "";
                strl = strl.substr(a1.length + a2.length + 2);
                a1 = parseInt(a1);
                a2 = parseInt(a2);
                for (var arg = 0; arg < a1; ++arg) {
                    if (arg <= a2) {
                        c1 += strl[arg * 2]
                    } else {
                        c1 += strl[a2 * 2 + (arg - a2)]
                    }
                }
                for (var arg = 0; arg < a2; ++arg) {
                    if (arg < a1) {
                        c2 += strl[1 + arg * 2]
                    } else {
                        c2 += strl[a1 * 2 + (arg - a1)]
                    }
                }
                var cj_1 = [c1, c2];
                var expDate = cj_1[0].substr(2, 4) + "-" + cj_1[0].substr(6, 2) + "-" + cj_1[0].substr(8, 2);
                f = cj_1[0].substr(17) == "jsdiagram" && rd.getTime() <= new Date(expDate).getTime();
                if (f) {
                    console.log("" + logMeInConsole() + cj_1[1] + ".")
                }
            };
            if (!checked && !canvas.suppressPaint) {
                checked = true;
                if (licenseKey == null) {
                    startXhr = new Date().getTime();
                    var fn = "diagram_lic.txt";
                    var xhr_1 = new XMLHttpRequest();
                    try {
                        var url = encodeURI(canvas.licenseLocation != "" ? canvas.licenseLocation : fn);
                        xhr_1.open("GET", url)
                    } catch (e) {
                        return
                    }
                    xhr_1.overrideMimeType("text/ plain");
                    xhr_1.onload = function(e) {
                        if (xhr_1.status === 200) {
                            var content = xhr_1.responseText;
                            if (content.length == 0) {
                                return
                            }
                            try {
                                if (content.indexOf(" ") >= 0) {
                                    return
                                }
                                lc(content)
                            } catch (e) {}
                        } else {}
                    };
                    xhr_1.onerror = function(e) {};
                    xhr_1.send()
                } else {
                    nowait = true;
                    try {
                        lc(licenseKey)
                    } catch (e) {}
                }
            }
            if (nowait || (!dt && new Date().getTime() - startXhr > 8000)) {
                dt = true
            }
            if (!dt) {
                return true
            }
            if (f) {
                return true
            }
            return false
        }
        var calculatedScrollSize = 0;
        MindFusion.registerClass(Canvas, "MindFusion.Drawing.Canvas", "Control")
    })(MindFusion.Drawing);
    MindFusion.Drawing.Canvas.create = function(element) {
        return mflayer.createControl(MindFusion.Drawing.Canvas, null, null, null, element)
    };
    (function(mdraw) {
        var CardinalSpline = mdraw.CardinalSpline = function(points, from, to) {
            if (from == null) {
                from = this.symmetricPoint(points[1], points[0])
            }
            if (to == null) {
                to = this.symmetricPoint(points[points.length - 2], points[points.length - 1])
            }
            this.splinePoints = points.slice();
            this.splinePoints.unshift(from);
            this.splinePoints.push(to);
            this.splinePoints = this.CatmullRomToBezier(this.splinePoints, 0.5)
        };
        CardinalSpline.prototype = {
            pen: "black",
            strokeThickness: 0,
            symmetricPoint: function(point, center) {
                var ptRes = new mdraw.Point(0, 0);
                var dx = point.x - center.x;
                var dy = point.y - center.y;
                ptRes.x = center.x - dx;
                ptRes.y = center.y - dy;
                return ptRes
            },
            CatmullRomToBezier: function(p, alpha) {
                var Vector = mdraw.Vector;
                var C = [];
                var epsilon = 0.00001;
                var startIndex = 1;
                var endIndex = p.length - 2;
                for (var i = startIndex; i < endIndex; ++i) {
                    var nextii = (i + 1) % p.length;
                    var nextnextii = (nextii + 1) % p.length;
                    var previi = i - 1 < 0 ? p.length - 1 : i - 1;
                    var p0 = p[previi];
                    var p1 = p[i];
                    var p2 = p[nextii];
                    var p3 = p[nextnextii];
                    var d1 = Vector.sub(p1, p0).length();
                    var d2 = Vector.sub(p2, p1).length();
                    var d3 = Vector.sub(p3, p2).length();
                    var b1, b2;
                    if (Math.abs(d1) < epsilon) {
                        b1 = p1
                    } else {
                        b1 = Vector.multiplyScalar(p2, Math.pow(d1, 2 * alpha));
                        b1 = Vector.sub(b1, Vector.multiplyScalar(p0, Math.pow(d2, 2 * alpha)));
                        b1 = Vector.add(b1, Vector.multiplyScalar(p1, (2 * Math.pow(d1, 2 * alpha) + 3 * Math.pow(d1, alpha) * Math.pow(d2, alpha) + Math.pow(d2, 2 * alpha))));
                        b1 = Vector.multiplyScalar(b1, 1 / (3 * Math.pow(d1, alpha) * (Math.pow(d1, alpha) + Math.pow(d2, alpha)))).toPoint()
                    }
                    if (Math.abs(d3) < epsilon) {
                        b2 = p2
                    } else {
                        b2 = Vector.multiplyScalar(p1, Math.pow(d3, 2 * alpha));
                        b2 = Vector.sub(b2, Vector.multiplyScalar(p3, Math.pow(d2, 2 * alpha)));
                        b2 = Vector.add(b2, Vector.multiplyScalar(p2, (2 * Math.pow(d3, 2 * alpha) + 3 * Math.pow(d3, alpha) * Math.pow(d2, alpha) + Math.pow(d2, 2 * alpha))));
                        b2 = Vector.multiplyScalar(b2, 1 / (3 * Math.pow(d3, alpha) * (Math.pow(d3, alpha) + Math.pow(d2, alpha)))).toPoint()
                    }
                    if (i == startIndex) {
                        C.push(p1)
                    }
                    C.push(b1);
                    C.push(b2);
                    C.push(p2)
                }
                return C
            },
            draw: function(context, drawShadow) {
                if (this.shadow && drawShadow != false) {
                    context.save();
                    this.shadow.apply(context)
                }
                context.strokeStyle = this.pen;
                context.lineWidth = (this.strokeThickness ? this.strokeThickness : 1) / context._mf_scale;
                mdraw.DashStyle.apply(context, this.strokeDashStyle);
                context.beginPath();
                var points = this.splinePoints;
                context.moveTo(points[0].x, points[0].y);
                for (var i = 0; i < points.length - 1; i += 3) {
                    context.bezierCurveTo(points[i + 1].x, points[i + 1].y, points[i + 2].x, points[i + 2].y, points[i + 3].x, points[i + 3].y)
                }
                context.stroke();
                if (this.shadow && drawShadow != false) {
                    context.restore()
                }
            },
            drawShadow: function(context) {
                if (this.shadow) {
                    context.save();
                    this.shadow.apply(context);
                    context.lineWidth = (this.strokeThickness ? this.strokeThickness : 1) / context._mf_scale;
                    context.beginPath();
                    var points = this.splinePoints;
                    context.moveTo(points[0].x, points[0].y);
                    for (var i = 0; i < points.length - 1; i += 3) {
                        context.bezierCurveTo(points[i + 1].x, points[i + 1].y, points[i + 2].x, points[i + 2].y, points[i + 3].x, points[i + 3].y)
                    }
                    context.strokeStyle = this.shadow.color;
                    context.stroke();
                    context.restore()
                }
            },
            addToContext: function(context) {
                context.beginPath();
                var points = this.splinePoints;
                context.moveTo(points[0].x, points[0].y);
                for (var i = 0; i < points.length - 1; i += 3) {
                    context.bezierCurveTo(points[i + 1].x, points[i + 1].y, points[i + 2].x, points[i + 2].y, points[i + 3].x, points[i + 3].y)
                }
            },
            createSvgElement: function(svgdoc) {
                var element = svgdoc.createElementNS("http://www.w3.org/2000/svg", "path");
                var points = this.splinePoints;
                var value = "M" + points[0].x + "," + points[0].y;
                for (var i = 0; i < points.length - 1; i += 3) {
                    value += " C" + points[i + 1].x + "," + points[i + 1].y + "," + points[i + 2].x + "," + points[i + 2].y + "," + points[i + 3].x + "," + points[i + 3].y
                }
                element.setAttribute("d", value);
                element.setAttribute("fill", "none");
                element.setAttribute("stroke", "black");
                element.setAttribute("stroke-width", this.strokeThickness ? this.strokeThickness / 4 : 1 / 4);
                return element
            }
        };
        MindFusion.registerClass(CardinalSpline, "MindFusion.Drawing.CardinalSpline")
    })(MindFusion.Drawing);
    (function(mdraw) {
        var Component = mdraw.Component = function() {};
        Component.prototype = {
            arrange: function(x, y, w, h) {
                this.x = x;
                this.y = y;
                this.actualWidth = w;
                this.actualHeight = h
            },
            effectiveMeasuredWidth: function() {
                return (this.width != null) ? this.width : this.desiredWidth
            },
            effectiveMeasuredHeight: function() {
                return (this.height != null) ? this.height : this.desiredHeight
            },
            add: function(value1, value2) {
                if (value1 == null) {
                    return value2
                }
                if (value2 == null) {
                    return value1
                }
                return value1 + value2
            },
            max: function(value1, value2) {
                if (value1 == null) {
                    return value2
                }
                if (value2 == null) {
                    return value1
                }
                return Math.max(value1, value2)
            },
            hitTest: function(point) {
                if (point.x < this.x || point.x > this.x + this.actualWidth || point.y < this.y || point.y > this.y + this.actualHeight) {
                    return null
                }
                return this
            },
            createSvgElement: function(svgdoc) {
                return null
            },
            visibility: mdraw.Visibility.Visible
        };
        MindFusion.registerClass(Component, "MindFusion.Drawing.Component")
    })(MindFusion.Drawing);
    (function(mdraw) {
        var Container = mdraw.Container = function(x, y) {
            mflayer.initializeBase(Container, this);
            this.x = x;
            this.y = y;
            this.content = []
        };
        Container.prototype = {
            draw: function(context, drawShadows, shadowsOnly) {
                if (this.invalidParent) {
                    this.invalidParent.updateCanvasElements();
                    this.invalidParent = null
                }
                if (this.x || this.y || this.clip || this.rotationAngle) {
                    context.save();
                    var savedTransform;
                    if (this.x !== undefined) {
                        context.translate(this.x, this.y);
                        savedTransform = context._mf_transform.clone();
                        context._mf_transform.translate(this.x, this.y)
                    }
                    if (this.rotationAngle) {
                        context.save();
                        context.translate(this.pivot.x, this.pivot.y);
                        context.rotate(this.rotationAngle * Math.PI / 180);
                        context.translate(-this.pivot.x, -this.pivot.y)
                    }
                    if (this.clip) {
                        if (mflayer.isInstanceOfType(MindFusion.Drawing.Rect, this.clip)) {
                            context.beginPath();
                            this.clip.drawPath(context);
                            context.clip()
                        } else {
                            if (mflayer.isInstanceOfType(MindFusion.Drawing.Path, this.clip)) {
                                this.clip.addToContext(context, false);
                                context.clip()
                            }
                        }
                    }
                    this.drawChildren(context, drawShadows, shadowsOnly);
                    if (this.rotationAngle) {
                        context.restore()
                    }
                    if (this.drawCallback) {
                        this.drawCallback(context, drawShadows, shadowsOnly)
                    }
                    if (savedTransform) {
                        context._mf_transform = savedTransform
                    }
                    context.restore()
                } else {
                    this.drawChildren(context, drawShadows, shadowsOnly);
                    if (this.drawCallback) {
                        this.drawCallback(context, drawShadows, shadowsOnly)
                    }
                }
            },
            drawChildren: function(context, drawShadows, shadowsOnly) {
                for (var i = 0; i < this.content.length; i++) {
                    var child = this.content[i];
                    var visibility = child.visibility;
                    if (typeof visibility == "undefined") {
                        visibility = mdraw.Visibility.Visible
                    }
                    if (visibility == mdraw.Visibility.Visible) {
                        if (shadowsOnly && child.drawShadow) {
                            child.drawShadow(context)
                        } else {
                            child.draw(context, drawShadows)
                        }
                    }
                }
            },
            createSvgElement: function(svgdoc) {
                var g = svgdoc.createElementNS("http://www.w3.org/2000/svg", "g");
                if (this.x || this.y || this.rotationAngle) {
                    var transform = "";
                    if (this.x || this.y) {
                        transform = "translate(" + this.x + " " + this.y + ")"
                    }
                    if (this.rotationAngle) {
                        transform += " rotate(" + this.rotationAngle + " " + this.pivot.x + " " + this.pivot.y + ")"
                    }
                    g.setAttribute("transform", transform)
                }
                if (this.clip) {
                    var clipId = "clip" + ++svgdoc._mf_clipCounter;
                    var clip = svgdoc.createElementNS("http://www.w3.org/2000/svg", "clipPath");
                    clip.setAttribute("id", clipId);
                    svgdoc._mf_defsElement.appendChild(clip);
                    var clipShape = this.clip.createSvgElement(svgdoc);
                    clip.appendChild(clipShape);
                    g.setAttribute("clip-path", "url(#" + clipId + ")")
                }
                var empty = true;
                for (var i = 0; i < this.content.length; i++) {
                    if (this.content[i].createSvgElement) {
                        var element = this.content[i].createSvgElement(svgdoc);
                        if (element != null) {
                            g.appendChild(element);
                            empty = false
                        }
                    }
                }
                if (empty) {
                    return null
                }
                return g
            }
        };
        MindFusion.registerClass(Container, "MindFusion.Drawing.Container", mdraw.Component)
    })(MindFusion.Drawing);
    MindFusion.Drawing.DistanceToSegment = function(p, a, b) {
        this.p = p;
        this.a = a;
        this.b = b
    };
    MindFusion.Drawing.DistanceToSegment.prototype = {
        distanceToSegment: function() {
            return Math.sqrt(this.distanceToSegmentSquared())
        },
        distanceToSegmentSquared: function() {
            if (this.a === this.b) {
                return this.distanceSq(this.p, this.a)
            }
            var dx = this.b.x - this.a.x;
            var dy = this.b.y - this.a.y;
            var dotProduct = (this.p.x - this.a.x) * dx + (this.p.y - this.a.y) * dy;
            if (dotProduct < 0) {
                return this.distanceSq(this.a, this.p)
            }
            dotProduct = (this.b.x - this.p.x) * dx + (this.b.y - this.p.y) * dy;
            if (dotProduct < 0) {
                return this.distanceSq(this.b, this.p)
            }
            return this.distanceToLineSquared(this.p, this.a, this.b)
        },
        distanceSq: function(pt1, pt2) {
            return (pt1.x - pt2.x) * (pt1.x - pt2.x) + (pt1.y - pt2.y) * (pt1.y - pt2.y)
        },
        distanceToLineSquared: function(p, a, b) {
            if (a === b) {
                return this.distanceSq(p, a)
            }
            var dx = b.x - a.x;
            var dy = b.y - a.y;
            var area = (p.y - a.y) * dx - (p.x - a.x) * dy;
            return area * area / (dx * dx + dy * dy)
        }
    };
    MindFusion.registerClass(MindFusion.Drawing.DistanceToSegment, "MindFusion.Drawing.DistanceToSegment");
    (function(mdraw) {
        var Ellipse = mdraw.Ellipse = function(x, y, width, height) {
            if (arguments.length == 1) {
                var rect = x;
                x = rect.x;
                y = rect.y;
                width = rect.width;
                height = rect.height
            }
            this.x = x;
            this.y = y;
            this.width = width;
            this.height = height;
            this.transform = new mdraw.Matrix()
        };
        Ellipse.prototype = {
            clone: function() {
                return new Ellipse(this.x, this.y, this.width, this.height)
            },
            draw: function(context) {
                context.save();
                context.transform.apply(context, this.transform.matrix());
                context.fillStyle = MindFusion.Diagramming.Utils.getBrush(context, this.brush, this.getBounds());
                context.strokeStyle = this.pen;
                context.lineWidth = (this.strokeThickness ? this.strokeThickness : 1) / context._mf_scale;
                mdraw.DashStyle.apply(context, this.strokeDashStyle);
                var x = this.x;
                var y = this.y;
                var w = this.width;
                var h = this.height;
                var kappa = 0.5522848;
                ox = (w / 2) * kappa, oy = (h / 2) * kappa, xe = x + w, ye = y + h, xm = x + w / 2, ym = y + h / 2;
                context.beginPath();
                context.moveTo(x, ym);
                context.bezierCurveTo(x, ym - oy, xm - ox, y, xm, y);
                context.bezierCurveTo(xm + ox, y, xe, ym - oy, xe, ym);
                context.bezierCurveTo(xe, ym + oy, xm + ox, ye, xm, ye);
                context.bezierCurveTo(xm - ox, ye, x, ym + oy, x, ym);
                context.closePath();
                context.fill();
                context.stroke();
                context.restore()
            },
            getBounds: function(rect) {
                return new MindFusion.Drawing.Rect(this.x, this.y, this.width, this.height)
            },
            toString: function() {
                return this.x + ", " + this.y + ", " + this.width + ", " + this.height
            },
            createSvgElement: function(svgdoc) {
                var cx = this.x + this.width / 2;
                var cy = this.y + this.height / 2;
                var rx = this.width / 2;
                var ry = this.height / 2;
                var ellipse = svgdoc.createElementNS("http://www.w3.org/2000/svg", "ellipse");
                if (this.transform) {
                    ellipse.setAttribute("transform", this.transform.svgMatrix())
                }
                ellipse.setAttribute("cx", cx);
                ellipse.setAttribute("cy", cy);
                ellipse.setAttribute("rx", rx);
                ellipse.setAttribute("ry", ry);
                return ellipse
            },
            transform: null,
            pen: "black",
            brush: "transparent"
        };
        MindFusion.registerClass(Ellipse, "MindFusion.Drawing.Ellipse")
    })(MindFusion.Drawing);
    (function(mdraw) {
        var Font = mdraw.Font = function(name, size, bold, italic, underline) {
            this.name = name;
            this.size = size;
            this.bold = bold;
            this.italic = italic;
            this.underline = underline
        };
        Font.prototype.toString = function(scale) {
            var size = this.size;
            if (scale) {
                size *= scale
            }
            var font = "";
            if (this.bold) {
                font += "bold "
            }
            if (this.italic) {
                font += "italic "
            }
            font += size + "px " + this.name;
            return font
        };
        Font.copy = function(font) {
            return new Font(font.name, font.size, font.bold, font.italic, font.underline)
        };
        Font.defaultFont = new Font("sans-serif", 3);
        MindFusion.registerClass(Font, "MindFusion.Drawing.Font")
    })(MindFusion.Drawing);
    MindFusion.Drawing.Gradient = function(col1, col2, ang) {
        this.color1 = col1;
        this.color2 = col2;
        this.angle = ang
    };
    MindFusion.Drawing.Gradient.tm = [32, 102, 111, 114, 32, 74, 97, 118, 97, 83, 99, 114, 105, 112, 116, 44, 32, 116, 114, 105, 97, 108, 32, 118, 101, 114, 115, 105, 111, 110];
    MindFusion.Drawing.Gradient.tm2 = [32, 102, 111, 114, 32, 65, 83, 80, 46, 78, 69, 84, 32, 77, 86, 67, 44, 32, 116, 114, 105, 97, 108, 32, 118, 101, 114, 115, 105, 111, 110];
    MindFusion.Drawing.Gradient.tm3 = [32, 102, 111, 114, 32, 65, 83, 80, 46, 78, 69, 84, 44, 32, 116, 114, 105, 97, 108, 32, 118, 101, 114, 115, 105, 111, 110];
    MindFusion.Drawing.Gradient.tm4 = [32, 102, 111, 114, 32, 65, 83, 80, 46, 78, 69, 84, 44, 32, 118, 53, 46, 52, 46, 49, 32, 98, 101, 116, 97];
    MindFusion.Drawing.Gradient.tm5 = [32, 102, 111, 114, 32, 74, 97, 118, 97, 83, 99, 114, 105, 112, 116, 44, 32, 86, 50, 46, 51, 46, 49, 32, 98, 101, 116, 97];
    MindFusion.Drawing.Gradient.tm6 = [32, 102, 111, 114, 32, 65, 83, 80, 46, 78, 69, 84, 32, 77, 86, 67, 44, 32, 49, 46, 55, 32, 98, 101, 116, 97, 32];
    MindFusion.registerClass(MindFusion.Drawing.Gradient, "MindFusion.Drawing.Gradient");
    MindFusion.Drawing.GraphicsUnit = {
        World: 0,
        Display: 1,
        Pixel: 2,
        Point: 3,
        Inch: 4,
        Document: 5,
        Millimeter: 6,
        WpfPoint: 7,
        Percent: 8,
        Centimeter: 9,
        unitsPerInch: function(unit) {
            switch (unit) {
                case this.Display:
                    return 100;
                case this.Document:
                    return 300;
                case this.Inch:
                    return 1;
                case this.Millimeter:
                    return 25.4;
                case this.Point:
                    return 72;
                case this.Pixel:
                    return 96;
                case this.WpfPoint:
                    return 96;
                case this.Centimeter:
                    return 2.54
            }
            return 1
        },
        convert: function(amount, sourceUnit, targetUnit) {
            return amount * this.unitsPerInch(targetUnit) / this.unitsPerInch(sourceUnit)
        },
        getPixel: function(unit) {
            return this.convert(1, this.Pixel, unit)
        },
        getMillimeter: function(unit) {
            return this.convert(1, this.Millimeter, unit)
        },
        getStandardDivisions: function(unit) {
            if (unit == this.Inch) {
                return 8
            }
            return 10
        }
    };
    (function(mdraw) {
        mdraw.Image = function(bounds) {
            this.loaded = false;
            this.image = new Image();
            this.bounds = bounds;
            this.transform = new mdraw.Matrix();
            this.clipPath = new mdraw.Path();
            this.type = this.constructor.__typeName;
            this.svg = false;
            this.imageAlign = mdraw.ImageAlign.Fit
        };
        mdraw.Image.prototype = {
            getType: function() {
                return this.type
            },
            setBounds: function(bounds, angle) {
                this.bounds = bounds;
                var matrix = new mdraw.Matrix();
                if (angle !== 0) {
                    matrix.rotateAt(angle, this.bounds.x + this.bounds.width / 2, this.bounds.y + this.bounds.height / 2)
                }
                this.rotationAngle = angle;
                this.transform = matrix
            },
            getBounds: function() {
                return this.bounds
            },
            draw: function(context) {
                if (this.image == null) {
                    return
                }
                if (this.image.src !== "" && this.loaded) {
                    if (!this.clipPath.empty()) {
                        context.save();
                        this.clipPath.addToContext(context);
                        context.restore();
                        context.save();
                        context.clip()
                    }
                    var scale = mdraw.GraphicsUnit.getPixel(context._mf_measureUnit);
                    var imgDocSize = this.svg ? {
                        x: this.bounds.width * scale,
                        y: this.bounds.height * scale
                    } : {
                        x: this.image.width * scale,
                        y: this.image.height * scale
                    };
                    var imgDocRect = this.getImageRect(this.bounds, imgDocSize);
                    context.save();
                    if (this.svg) {
                        var imageBounds = this.applyDiagramTransform(context, imgDocRect);
                        context.setTransform(1, 0, 0, 1, 0, 0);
                        var matrix = new mdraw.Matrix();
                        if (this.rotationAngle !== 0) {
                            matrix.rotateAt(this.rotationAngle, imageBounds.x + imageBounds.width / 2, imageBounds.y + imageBounds.height / 2)
                        }
                        context.transform.apply(context, matrix.matrix());
                        context.drawImage(this.image, imageBounds.x, imageBounds.y, imageBounds.width, imageBounds.height)
                    } else {
                        context.transform.apply(context, this.transform.matrix());
                        context.drawImage(this.image, imgDocRect.x, imgDocRect.y, imgDocRect.width, imgDocRect.height)
                    }
                    context.restore();
                    if (!this.clipPath.empty()) {
                        context.restore()
                    }
                }
            },
            applyDiagramTransform: function(context, imgDocRect) {
                var bounds = imgDocRect.clone();
                var matrix = context._mf_transform;
                var p = bounds.topLeft();
                matrix.transformPoint(p);
                bounds.x = p.x;
                bounds.y = p.y;
                bounds.width *= context._mf_scale;
                bounds.height *= context._mf_scale;
                return bounds
            },
            measure: function(maxWidth, maxHeight) {
                var GraphicsUnit = mdraw.GraphicsUnit;
                var ctx = mdraw.Component.context;
                this.desiredWidth = this.image.width ? GraphicsUnit.convert(this.image.width, GraphicsUnit.Pixel, ctx.measureUnit) : null;
                this.desiredHeight = this.image.height ? GraphicsUnit.convert(this.image.height, GraphicsUnit.Pixel, ctx.measureUnit) : null
            },
            effectiveMeasuredWidth: function() {
                return this.desiredWidth
            },
            effectiveMeasuredHeight: function() {
                return this.desiredHeight
            },
            getImageRect: function(parentRect, imgSize) {
                var imageAlign = this.imageAlign;
                var xoff = 0,
                    yoff = 0,
                    picw = imgSize.x,
                    pich = imgSize.y,
                    rect = parentRect;
                switch (imageAlign) {
                    case mdraw.ImageAlign.TopLeft:
                        xoff = rect.left();
                        yoff = rect.top();
                        break;
                    case mdraw.ImageAlign.BottomLeft:
                        xoff = rect.left();
                        yoff = rect.bottom() - pich;
                        break;
                    case mdraw.ImageAlign.TopRight:
                        xoff = rect.right() - picw;
                        yoff = rect.top();
                        break;
                    case mdraw.ImageAlign.BottomRight:
                        xoff = rect.right() - picw;
                        yoff = rect.bottom() - pich;
                        break;
                    case mdraw.ImageAlign.Center:
                        xoff = (rect.right() + rect.left() - picw) / 2;
                        yoff = (rect.bottom() + rect.top() - pich) / 2;
                        break;
                    case mdraw.ImageAlign.TopCenter:
                        xoff = rect.x + rect.width / 2 - picw / 2;
                        yoff = rect.y;
                        break;
                    case mdraw.ImageAlign.BottomCenter:
                        xoff = rect.x + rect.width / 2 - picw / 2;
                        yoff = rect.bottom() - pich;
                        break;
                    case mdraw.ImageAlign.MiddleLeft:
                        xoff = rect.x;
                        yoff = rect.y + rect.height / 2 - pich / 2;
                        break;
                    case mdraw.ImageAlign.MiddleRight:
                        xoff = rect.right() - picw;
                        yoff = rect.y + rect.height / 2 - pich / 2;
                        break;
                    case mdraw.ImageAlign.Fit:
                        var h = rect.height;
                        var w = rect.width;
                        if (h == 0) {
                            break
                        }
                        var ratioCtrl = w / h;
                        var ratioPic = picw / pich;
                        if (ratioCtrl > ratioPic) {
                            pich = h;
                            picw = (ratioPic * pich);
                            yoff = rect.top();
                            xoff = (rect.right() + rect.left() - picw) / 2
                        } else {
                            picw = w;
                            if (ratioPic == 0) {
                                break
                            }
                            pich = (picw / ratioPic);
                            xoff = rect.left();
                            yoff = (rect.bottom() + rect.top() - pich) / 2
                        }
                        break;
                    case mdraw.ImageAlign.Stretch:
                        picw = rect.right() - rect.left();
                        pich = rect.bottom() - rect.top();
                        xoff = rect.left();
                        yoff = rect.top();
                        break;
                    case mdraw.ImageAlign.Tile:
                        xoff = rect.left();
                        yoff = rect.top();
                        break
                }
                return new mdraw.Rect(xoff, yoff, picw, pich)
            },
            getDefaultProperty: function() {
                return this.image.src
            },
            setDefaultProperty: function(value) {
                this.image.src = value
            },
            createSvgElement: function(svgdoc) {
                var element = svgdoc.createElementNS("http://www.w3.org/2000/svg", "image");
                element.setAttributeNS("http://www.w3.org/1999/xlink", "href", this.image.src);
                var imgSize = {
                    x: this.image.width,
                    y: this.image.height
                };
                var imgRect = this.getImageRect(this.bounds, imgSize);
                element.setAttribute("x", imgRect.x + "px");
                element.setAttribute("y", imgRect.y + "px");
                element.setAttribute("width", imgRect.width + "px");
                element.setAttribute("height", imgRect.height + "px");
                if (this.transform) {
                    element.setAttribute("transform", this.transform.svgMatrix())
                }
                return element
            },
            bounds: null,
            transform: null,
            image: null,
            horizontalAlignment: mdraw.LayoutAlignment.Stretch,
            verticalAlignment: mdraw.LayoutAlignment.Stretch
        };
        MindFusion.registerClass(mdraw.Image, "MindFusion.Drawing.Image")
    })(MindFusion.Drawing);
    (function(mdraw) {
        var Line = mdraw.Line = function(x1, y1, x2, y2) {
            this.x1 = x1;
            this.y1 = y1;
            this.x2 = x2;
            this.y2 = y2;
            this.transform = new mdraw.Matrix();
            this.pen = "black";
            this.strokeThickness = 0
        };
        Line.prototype = {
            draw: function(context, drawShadow) {
                if (this.clipPath && !this.clipPath.empty()) {
                    this.clipPath.addToContext(context);
                    context.save();
                    context.clip()
                }
                if (this.shadow && drawShadow != false) {
                    context.save();
                    this.shadow.apply(context)
                }
                context.strokeStyle = this.pen;
                context.lineWidth = (this.strokeThickness ? this.strokeThickness : 1) / context._mf_scale;
                mdraw.DashStyle.apply(context, this.strokeDashStyle);
                context.beginPath();
                context.moveTo(this.x1, this.y1);
                context.lineTo(this.x2, this.y2);
                context.stroke();
                if (this.shadow && drawShadow != false) {
                    context.restore()
                }
                if (this.clipPath && !this.clipPath.empty()) {
                    context.restore()
                }
            },
            drawShadow: function(context) {
                if (this.shadow) {
                    context.save();
                    this.shadow.apply(context);
                    context.lineWidth = (this.strokeThickness ? this.strokeThickness : 1) / context._mf_scale;
                    context.beginPath();
                    context.moveTo(this.x1, this.y1);
                    context.lineTo(this.x2, this.y2);
                    context.strokeStyle = this.shadow.color;
                    context.stroke();
                    context.restore()
                }
            },
            setBounds: function(point1, point2) {
                this.x1 = point1.x;
                this.y1 = point1.y;
                this.x2 = point2.x;
                this.y2 = point2.y
            },
            setPen: function(pen) {
                this.pen = pen
            },
            createSvgElement: function(svgdoc) {
                var line = svgdoc.createElementNS("http://www.w3.org/2000/svg", "line");
                if (this.transform) {
                    line.setAttribute("transform", this.transform.svgMatrix())
                }
                line.setAttribute("x1", this.x1);
                line.setAttribute("x2", this.x2);
                line.setAttribute("y1", this.y1);
                line.setAttribute("y2", this.y2);
                if (this.pen) {
                    line.setAttribute("stroke", this.pen)
                }
                line.setAttribute("stroke-width", this.strokeThickness ? this.strokeThickness / 4 : 1 / 4);
                return line
            }
        };
        MindFusion.registerClass(Line, "MindFusion.Drawing.Line")
    })(MindFusion.Drawing);
    MindFusion.Drawing.Matrix = function() {
        this.elements = [];
        this.elements[0] = 1;
        this.elements[1] = 0;
        this.elements[2] = 0;
        this.elements[3] = 1;
        this.elements[4] = 0;
        this.elements[5] = 0
    };
    MindFusion.Drawing.Matrix.fromValues = function(values) {
        var m = new MindFusion.Drawing.Matrix();
        m.elements = values;
        return m
    };
    MindFusion.Drawing.Matrix.prototype = {
        matrix: function() {
            return this.elements
        },
        isIdentity: function() {
            if (this.elements[0] == 1 && this.elements[1] == 0 && this.elements[2] == 0 && this.elements[3] == 1 && this.elements[4] == 0 && this.elements[5] == 0) {
                return true
            }
            return false
        },
        clone: function() {
            var matrix = new MindFusion.Drawing.Matrix();
            matrix.elements = this.elements.slice(0);
            return matrix
        },
        translate: function(x, y) {
            this.elements[4] += this.elements[0] * x + this.elements[2] * y;
            this.elements[5] += this.elements[1] * x + this.elements[3] * y
        },
        scale: function(sx, sy) {
            this.elements[0] *= sx;
            this.elements[1] *= sx;
            this.elements[2] *= sy;
            this.elements[3] *= sy
        },
        scaleAtCenter: function(sx, sy, rect) {
            var x = rect.x + rect.width / 2;
            var y = rect.y + rect.height / 2;
            this.translate(x, y);
            this.scale(sx, sy);
            this.translate(-x, -y)
        },
        rotate: function(angle) {
            angle = (angle * Math.PI) / 180;
            var sin = Math.sin(angle).toFixed(3);
            var cos = Math.cos(angle).toFixed(3);
            var a = this.elements[0];
            var b = this.elements[1];
            var c = this.elements[2];
            var d = this.elements[3];
            this.elements[0] = a * cos - b * sin;
            this.elements[1] = a * sin + b * cos;
            this.elements[2] = c * cos - d * sin;
            this.elements[3] = c * sin + d * cos
        },
        rotateAt: function(angle, x, y) {
            if (x instanceof MindFusion.Drawing.Point) {
                y = x.y;
                x = x.x
            }
            angle = angle * Math.PI / 180;
            this.translate(x, y);
            var sin = Math.sin(angle).toFixed(3);
            var cos = Math.cos(angle).toFixed(3);
            var a = this.elements[0];
            var b = this.elements[1];
            var c = this.elements[2];
            var d = this.elements[3];
            this.elements[0] = a * cos - b * sin;
            this.elements[1] = a * sin + b * cos;
            this.elements[2] = c * cos - d * sin;
            this.elements[3] = c * sin + d * cos;
            this.translate(-x, -y)
        },
        invert: function() {
            var det = this.elements[0] * (this.elements[3] * 1 - this.elements[5] * 0) - this.elements[1] * (this.elements[2] * 1 - 0 * this.elements[4]) + 0 * (this.elements[2] * this.elements[5] - this.elements[3] * this.elements[4]);
            var invdet = 1 / det;
            var result = new MindFusion.Drawing.Matrix();
            result.elements[0] = (this.elements[3] * 1 - this.elements[5] * 0) * invdet;
            result.elements[1] = (this.elements[4] * this.elements[5] - this.elements[1] * 1) * invdet;
            result.elements[2] = (0 * this.elements[4] - this.elements[2] * 1) * invdet;
            result.elements[3] = (this.elements[0] * 1 - 0 * this.elements[4]) * invdet;
            result.elements[4] = (this.elements[2] * this.elements[5] - this.elements[4] * this.elements[3]) * invdet;
            result.elements[5] = (this.elements[4] * this.elements[1] - this.elements[0] * this.elements[5]) * invdet;
            return result
        },
        transformPoint: function(point) {
            var x = point.x;
            var y = point.y;
            point.x = this.elements[0] * x + this.elements[2] * y + this.elements[4];
            point.y = this.elements[1] * x + this.elements[3] * y + this.elements[5]
        },
        transformPoints: function(points) {
            for (var i = 0; i < points.length; i++) {
                this.transformPoint(points[i])
            }
        },
        transformRect: function(rect) {
            var points = rect.getCornerPoints();
            this.transformPoints(points);
            return MindFusion.Drawing.Rect.boundingRect(points)
        },
        svgMatrix: function() {
            var transform = "matrix(";
            for (var i = 0; i < this.elements.length; i++) {
                transform += this.elements[i];
                if (i < this.elements.length - 1) {
                    transform += ","
                } else {
                    transform += ")"
                }
            }
            return transform
        }
    };
    MindFusion.registerClass(MindFusion.Drawing.Matrix, "MindFusion.Drawing.Matrix");
    (function(mdraw) {
        var Path = mdraw.Path = function(pathString) {
            this.shapeImpl = null;
            this.builder = null;
            this.path = null;
            this.brush = null;
            this.pen = null;
            this.text = null;
            this.positionX = null;
            this.positionY = null;
            this.minX = Number.MAX_VALUE;
            this.minY = Number.MAX_VALUE;
            this.maxX = 0;
            this.maxY = 0;
            this.strokeThickness = 0;
            this.lineJoin = "miter";
            this.transform = new mdraw.Matrix();
            this.svgPath = pathString;
            this.init();
            if (pathString != null) {
                var separators = ["M", "L", "B", "Q", "A", "Z", "C", "E", "R", "U"];
                var i = 0;
                while (i < separators.length) {
                    var sep = separators[i];
                    pathString = pathString.replace(new RegExp(sep, "g"), ":" + sep);
                    i++
                }
                var cmdStrings = pathString.split(":");
                this.commands = cmdStrings.filter(String);
                this.parse();
                this.done()
            }
        };
        Path.prototype = {
            setBounds: function(bounds) {
                this.bounds = bounds;
                this.updatePosition()
            },
            init: function() {
                this.builder = []
            },
            clone: function() {
                var path = new Path();
                path.minX = this.minX;
                path.minY = this.minY;
                path.maxX = this.maxX;
                path.maxY = this.maxY;
                path.builder = this.builder;
                path.pen = this.pen;
                path.brush = this.brush;
                path.transform = new mdraw.Matrix();
                return path
            },
            getType: function() {
                return this.constructor.__typeName
            },
            empty: function() {
                if (this.builder.length === 0) {
                    return true
                }
                return false
            },
            parse: function() {
                mdraw.PathParser.parse(this.commands, this)
            },
            addToContext: function(context, addTransform) {
                if (addTransform == false) {
                    context.save()
                }
                if (this.transform) {
                    context.transform.apply(context, this.transform.matrix())
                }
                context.beginPath();
                var p = this.builder;
                var l = p.length;
                if (l > 0) {
                    var i = 0;
                    while (i < l) {
                        switch (p[i]) {
                            case "M":
                                context.moveTo(p[i + 1], p[i + 3]);
                                i += 4;
                                break;
                            case "L":
                                context.lineTo(p[i + 1], p[i + 3]);
                                i += 4;
                                break;
                            case "C":
                                context.bezierCurveTo(p[i + 1], p[i + 3], p[i + 5], p[i + 7], p[i + 9], p[i + 11]);
                                i += 12;
                                break;
                            case "Q":
                                context.quadraticCurveTo(p[i + 1], p[i + 3], p[i + 5], p[i + 7]);
                                i += 8;
                                break;
                            case "A":
                                context.arc(p[i + 1], p[i + 3], p[i + 5], p[i + 7], p[i + 9], p[i + 11]);
                                i += 12;
                                break;
                            case "R":
                                context.rect(p[i + 1], p[i + 3], p[i + 5], p[i + 7]);
                                i += 8;
                                break;
                            case "E":
                                context.moveTo(p[i + 1], p[i + 3] - p[i + 7] / 2);
                                context.bezierCurveTo(p[i + 1] + p[i + 5] / 2, p[i + 3] - p[i + 7] / 2, p[i + 1] + p[i + 5] / 2, p[i + 3] + p[i + 7] / 2, p[i + 1], p[i + 3] + p[i + 7] / 2);
                                context.bezierCurveTo(p[i + 1] - p[i + 5] / 2, p[i + 3] + p[i + 7] / 2, p[i + 1] - p[i + 5] / 2, p[i + 3] - p[i + 7] / 2, p[i + 1], p[i + 3] - p[i + 7] / 2);
                                i += 8;
                                break;
                            case "U":
                                context.roundRect(p[i + 1], p[i + 3], p[i + 5], p[i + 7], p[i + 9]);
                                i += 10;
                                break;
                            case "Z":
                                if (context.closePath) {
                                    context.closePath()
                                }
                            default:
                                i += 1
                        }
                    }
                }
                if (addTransform == false) {
                    context.restore()
                }
            },
            draw: function(context, drawShadow) {
                context.save();
                this.addToContext(context);
                if (this.shadow && drawShadow != false) {
                    this.shadow.apply(context)
                }
                if (this.brush) {
                    context.fillStyle = MindFusion.Diagramming.Utils.getBrush(context, this.brush, this.getBounds());
                    context.fill()
                }
                context.restore();
                if (this.pen) {
                    context.strokeStyle = MindFusion.Diagramming.Utils.getBrush(context, this.pen, this.getBounds(), true);
                    context.lineWidth = (this.strokeThickness ? this.strokeThickness : 1) / context._mf_scale;
                    mdraw.DashStyle.apply(context, this.strokeDashStyle);
                    context.lineJoin = this.lineJoin;
                    context.stroke()
                }
            },
            drawShadow: function(context) {
                if (this.shadow) {
                    context.save();
                    this.addToContext(context);
                    this.shadow.apply(context);
                    if (this.brush) {
                        context.fillStyle = this.shadow.color;
                        context.fill()
                    } else {
                        if (this.pen) {
                            context.strokeStyle = this.shadow.color;
                            context.lineWidth = (this.strokeThickness ? this.strokeThickness : 1) / context._mf_scale;
                            context.lineJoin = this.lineJoin;
                            context.stroke()
                        }
                    }
                    context.restore()
                }
            },
            done: function() {
                if (this.builder) {
                    this.path = this.builder.join("")
                }
                this.updatePathDefinition()
            },
            moveTo: function(x, y) {
                this.builder.push("M");
                this.builder.push(x);
                this.builder.push(",");
                this.builder.push(y);
                this.positionX = x;
                this.positionY = y;
                this.expandRect(x, y)
            },
            lineTo: function(x, y) {
                this.builder.push("L");
                this.builder.push(x);
                this.builder.push(",");
                this.builder.push(y);
                this.positionX = x;
                this.positionY = y;
                this.expandRect(x, y)
            },
            bezierTo: function(x1, y1, x2, y2, x3, y3) {
                this.builder.push("C");
                this.builder.push(x1);
                this.builder.push(",");
                this.builder.push(y1);
                this.builder.push(",");
                this.builder.push(x2);
                this.builder.push(",");
                this.builder.push(y2);
                this.builder.push(",");
                this.builder.push(x3);
                this.builder.push(",");
                this.builder.push(y3);
                this.positionX = x3;
                this.positionY = y3;
                this.expandRect(x1, y1);
                this.expandRect(x2, y2);
                this.expandRect(x3, y3)
            },
            arcTo: function(x, y, radius, startAngle, endAngle, anticlockwise) {
                this.builder.push("A");
                this.builder.push(x);
                this.builder.push(",");
                this.builder.push(y);
                this.builder.push(",");
                this.builder.push(radius);
                this.builder.push(",");
                this.builder.push(startAngle);
                this.builder.push(",");
                this.builder.push(endAngle);
                this.builder.push(",");
                this.builder.push(anticlockwise);
                this.positionX = x;
                this.positionY = y;
                var r = +radius;
                this.expandRect(+x - r, +y - r);
                this.expandRect(+x + r, +y + r)
            },
            quadraticCurveTo: function(x1, y1, x, y) {
                this.builder.push("Q");
                this.builder.push(x1);
                this.builder.push(",");
                this.builder.push(y1);
                this.builder.push(",");
                this.builder.push(x);
                this.builder.push(",");
                this.builder.push(y);
                this.positionX = x;
                this.positionY = y;
                this.expandRect(x1, y1);
                this.expandRect(x, y)
            },
            addRect: function(x, y, width, height) {
                this.builder.push("R");
                this.builder.push(x);
                this.builder.push(",");
                this.builder.push(y);
                this.builder.push(",");
                this.builder.push(width);
                this.builder.push(",");
                this.builder.push(height);
                this.positionX = x + width;
                this.positionY = y + height;
                this.expandRect(x, y);
                this.expandRect(x + width, y + height)
            },
            addRoundRect: function(bounds, cornerRadius) {
                this.moveTo(bounds.x, bounds.y + cornerRadius);
                this.lineTo(bounds.x, bounds.y + bounds.height - cornerRadius);
                this.quadraticCurveTo(bounds.x, bounds.y + bounds.height, bounds.x + cornerRadius, bounds.y + bounds.height);
                this.lineTo(bounds.x + bounds.width - cornerRadius, bounds.y + bounds.height);
                this.quadraticCurveTo(bounds.x + bounds.width, bounds.y + bounds.height, bounds.x + bounds.width, bounds.y + bounds.height - cornerRadius);
                this.lineTo(bounds.x + bounds.width, bounds.y + cornerRadius);
                this.quadraticCurveTo(bounds.x + bounds.width, bounds.y, bounds.x + bounds.width - cornerRadius, bounds.y);
                this.lineTo(bounds.x + cornerRadius, bounds.y);
                this.quadraticCurveTo(bounds.x, bounds.y, bounds.x, bounds.y + cornerRadius)
            },
            roundRect: function(x1, y1, x2, y2, radius) {
                var bounds = MindFusion.Drawing.Rect.fromPoints(new MindFusion.Drawing.Point(x1, y1), new MindFusion.Drawing.Point(x2, y2));
                this.addRoundRect(bounds, radius)
            },
            addEllipse: function(x, y, width, height) {
                this.builder.push("E");
                this.builder.push(x);
                this.builder.push(",");
                this.builder.push(y);
                this.builder.push(",");
                this.builder.push(width);
                this.builder.push(",");
                this.builder.push(height);
                this.positionX = x;
                this.positionY = y;
                this.expandRect(x - width, y - height);
                this.expandRect(x + width, y + height)
            },
            close: function() {
                this.builder.push("Z")
            },
            setBrush: function(brush) {
                this.brush = brush;
                this.updatePathDefinition()
            },
            setPen: function(pen) {
                this.pen = pen;
                this.updatePathDefinition()
            },
            setText: function(text) {
                this.text = text;
                this.updatePathDefinition()
            },
            create: function(container) {},
            getBounds: function() {
                return new mdraw.Rect(this.minX, this.minY, this.maxX - this.minX, this.maxY - this.minY)
            },
            expandRect: function(x, y) {
                this.minX = Math.min(this.minX, x);
                this.minY = Math.min(this.minY, y);
                this.maxX = Math.max(this.maxX, x);
                this.maxY = Math.max(this.maxY, y)
            },
            updatePosition: function() {},
            updatePathDefinition: function() {},
            createSvgElement: function(svgdoc) {
                var element = svgdoc.createElementNS("http://www.w3.org/2000/svg", "path");
                if (this.transform) {
                    element.setAttribute("transform", this.transform.svgMatrix())
                }
                if (this.svgPath) {
                    element.setAttribute("d", this.svgPath || this.path)
                } else {
                    if (!this.path) {
                        this.done()
                    }
                    element.setAttribute("d", this.path)
                }
                if (this.brush) {
                    element.setAttribute("fill", MindFusion.Diagramming.Utils.getBrush(null, this.brush, this.getBounds()))
                } else {
                    element.setAttribute("fill", "none")
                }
                if (this.pen) {
                    element.setAttribute("stroke", this.pen)
                }
                element.setAttribute("stroke-width", this.strokeThickness ? this.strokeThickness / 4 : 1 / 4);
                return element
            },
            transform: null
        };
        Path.fromPoints = function(points) {
            var path = new Path(null);
            for (var i = 0; i < points.length; i++) {
                path.moveTo(points[i].x, points[i].y)
            }
            return path
        };
        MindFusion.registerClass(Path, "MindFusion.Drawing.Path")
    })(MindFusion.Drawing);
    (function(mdraw) {
        var PathParser = mdraw.PathParser = function() {};
        PathParser.parse = function(commands, renderer) {
            var currentX = 0;
            var currentY = 0;
            for (var i = 0; i < commands.length; i++) {
                var command = commands[i];
                var c = commands[i][0];
                switch (c) {
                    case "M":
                        command = command.substring(1, command.length);
                        var x = +command.split(",")[0];
                        var y = +command.split(",")[1];
                        renderer.moveTo(x, y);
                        currentX = x;
                        currentY = y;
                        break;
                    case "L":
                        command = command.substring(1, command.length);
                        var x = +command.split(",")[0];
                        var y = +command.split(",")[1];
                        renderer.lineTo(x, y);
                        currentX = x;
                        currentY = y;
                        break;
                    case "B":
                        command = command.substring(1, command.length);
                        var x1 = +command.split(",")[0];
                        var y1 = +command.split(",")[1];
                        var x2 = +command.split(",")[2];
                        var y2 = +command.split(",")[3];
                        var x3 = +command.split(",")[4];
                        var y3 = +command.split(",")[5];
                        renderer.bezierTo(x2, y2, x3, y3, x1, y1);
                        currentX = x1;
                        currentY = y1;
                        break;
                    case "C":
                        command = command.substring(1, command.length);
                        var x1 = +command.split(",")[0];
                        var y1 = +command.split(",")[1];
                        var x2 = +command.split(",")[2];
                        var y2 = +command.split(",")[3];
                        var x3 = +command.split(",")[4];
                        var y3 = +command.split(",")[5];
                        renderer.bezierTo(x1, y1, x2, y2, x3, y3);
                        currentX = x3;
                        currentY = y3;
                        break;
                    case "Q":
                        command = command.substring(1, command.length);
                        var x1 = +command.split(",")[0];
                        var y1 = +command.split(",")[1];
                        var x = +command.split(",")[2];
                        var y = +command.split(",")[3];
                        renderer.quadraticCurveTo(x1, y1, x, y);
                        currentX = x;
                        currentY = y;
                        break;
                    case "A":
                        command = command.substring(1, command.length);
                        var radiusX = +command.split(",")[0];
                        var radiusY = +command.split(",")[1];
                        var angleRotation = +command.split(",")[2];
                        var isLargeArc = +command.split(",")[3];
                        var isCounterclockwise = +command.split(",")[4];
                        isCounterclockwise = isCounterclockwise == 0 ? 1 : 0;
                        var x = +command.split(",")[5];
                        var y = +command.split(",")[6];
                        var pt1 = new MindFusion.Drawing.Point(currentX, currentY);
                        var pt2 = new MindFusion.Drawing.Point(x, y);
                        var matx = new MindFusion.Drawing.Matrix();
                        matx.rotate(-angleRotation);
                        matx.scale(radiusY / radiusX, 1);
                        matx.transformPoint(pt1);
                        matx.transformPoint(pt2);
                        var midPoint = new MindFusion.Drawing.Point((pt1.x + pt2.x) / 2, (pt1.y + pt2.y) / 2);
                        var vect = new MindFusion.Drawing.Vector(pt2.x - pt1.x, pt2.y - pt1.y);
                        var halfChord = vect.length() / 2;
                        var vectRotated;
                        if (isLargeArc == isCounterclockwise) {
                            vectRotated = new MindFusion.Drawing.Vector(-vect.y, vect.x)
                        } else {
                            vectRotated = new MindFusion.Drawing.Vector(vect.y, -vect.x)
                        }
                        if (vectRotated.x != 0 || vectRotated.y != 0) {
                            vectRotated = vectRotated.normalize()
                        }
                        var d2 = radiusY * radiusY - halfChord * halfChord;
                        var centerDistance = d2 > 0 ? Math.sqrt(d2) : 0;
                        var center = new MindFusion.Drawing.Point(midPoint.x + centerDistance * vectRotated.x, midPoint.y + centerDistance * vectRotated.y);
                        var angle1 = Math.atan2(pt1.y - center.y, pt1.x - center.x);
                        var angle2 = Math.atan2(pt2.y - center.y, pt2.x - center.x);
                        if (isLargeArc == (Math.abs(angle2 - angle1) < Math.PI)) {
                            if (angle1 < angle2) {
                                angle1 += 2 * Math.PI
                            } else {
                                angle2 += 2 * Math.PI
                            }
                        }
                        startAngle = (angle1 * 180 / Math.PI) % 360;
                        if (isCounterclockwise) {
                            angle1 = (angle1 < 0) ? 2 * Math.PI - Math.abs(angle1) : angle1;
                            angle2 = (angle2 < 0) ? 2 * Math.PI - Math.abs(angle2) : angle2
                        }
                        var endAngle = (angle2 * 180 / Math.PI) % 360;
                        sweepAngle = ((angle2 - angle1) * 180 / Math.PI) % 360;
                        startAngle = (startAngle < 0) ? (360 - Math.abs(startAngle)) : startAngle;
                        if (!isCounterclockwise) {
                            sweepAngle = (sweepAngle < 0) ? (360 - Math.abs(sweepAngle)) : sweepAngle
                        }
                        matx = matx.invert();
                        matx.transformPoint(center);
                        startAngle = startAngle * Math.PI / 180;
                        endAngle = endAngle * Math.PI / 180;
                        if (startAngle == endAngle) {
                            endAngle += 2 * Math.PI
                        }
                        renderer.arcTo(center.x, center.y, radiusX, startAngle, endAngle, isCounterclockwise);
                        currentX = x;
                        currentY = y;
                        break;
                    case "U":
                        command = command.substring(1, command.length);
                        var x1 = +command.split(",")[0];
                        var y1 = +command.split(",")[1];
                        var x2 = +command.split(",")[2];
                        var y2 = +command.split(",")[3];
                        var radius = +command.split(",")[4];
                        renderer.roundRect(x1, y1, x2, y2, radius);
                        currentX = x1;
                        currentY = y1;
                        break;
                    case "Z":
                        renderer.close();
                        break
                }
            }
        };
        MindFusion.registerClass(PathParser, "MindFusion.Drawing.PathParser")
    })(MindFusion.Drawing);
    MindFusion.Drawing.Point = function(x, y) {
        this.x = x;
        this.y = y;
        this.type = this.constructor.__typeName
    };
    MindFusion.Drawing.Point.distance = function(point1, point2) {
        return Math.sqrt(Math.pow((point2.x - point1.x), 2) + Math.pow((point2.y - point1.y), 2))
    };
    MindFusion.Drawing.Point.angleBetween = function(point1, point2) {
        var dy = point2.y - point1.y;
        var dx = point2.x - point1.x;
        return Math.atan2(dy, dx) / Math.PI * 180
    };
    MindFusion.Drawing.Point.addVector = function(point, vector) {
        var p = point.clone();
        p.addVector(vector);
        return p
    };
    MindFusion.Drawing.Point.prototype = {
        getType: function() {
            return this.type
        },
        empty: function() {
            return (this.x === 0 && this.y === 0)
        },
        distance: function(point) {
            return Math.sqrt(Math.pow((this.x - point.x), 2) + Math.pow((this.y - point.y), 2))
        },
        angleBetween: function(point) {
            var dy = point.y - this.y;
            var dx = point.x - this.x;
            return Math.atan2(dy, dx) / Math.PI * 180
        },
        addVector: function(vector) {
            this.x += vector.x;
            this.y += vector.y;
            return this
        },
        newWithOffset: function(dx, dy) {
            var p = this.clone();
            p.x += dx;
            p.y += dy;
            return p
        },
        equals: function(point) {
            if (!point) {
                return false
            }
            return (this.x === point.x && this.y === point.y)
        },
        clone: function() {
            var point = new MindFusion.Drawing.Point(this.x, this.y);
            return point
        }
    };
    MindFusion.registerClass(MindFusion.Drawing.Point, "MindFusion.Drawing.Point");
    (function(mdraw) {
        var ArrayList = MindFusion.Collections.ArrayList;
        var Rect = mdraw.Rect = function(x, y, width, height) {
            if (y instanceof MindFusion.Drawing.Size) {
                width = y.width;
                height = y.height
            }
            if (x instanceof MindFusion.Drawing.Point) {
                y = x.y;
                x = x.x
            }
            this.x = x;
            this.y = y;
            this.width = width;
            this.height = height;
            this.transform = new mdraw.Matrix();
            this.type = this.constructor.__typeName
        };
        Rect.fromLTRB = function(l, t, r, b) {
            return new Rect(Math.min(l, r), Math.min(t, b), Math.abs(r - l), Math.abs(b - t))
        };
        Rect.fromArgs = function(args) {
            return new Rect(args[0], args[1], args[2], args[3])
        };
        Rect.fromPoints = function(point1, point2) {
            return Rect.fromLTRB(point1.x, point1.y, point2.x, point2.y)
        };
        Rect.fromCenterAndSize = function(point, size) {
            var w = size.width;
            var h = size.height;
            return new Rect(point.x - w / 2, point.y - h / 2, w, h)
        };
        Rect.fromPositionAndSize = function(point, size) {
            return new Rect(point.x, point.y, size.width, size.height)
        };
        Rect.fromVertex = function(v) {
            var w = v.width;
            var h = v.height;
            return new Rect(v.x - w / 2, v.y - h / 2, w, h)
        };
        Rect.boundingRect = function(points) {
            var l = Number.MAX_VALUE;
            var t = Number.MAX_VALUE;
            var r = Number.MIN_VALUE;
            var b = Number.MIN_VALUE;
            ArrayList.forEach(points, function(point) {
                l = Math.min(l, point.x);
                t = Math.min(t, point.y);
                r = Math.max(r, point.x);
                b = Math.max(b, point.y)
            });
            return Rect.fromLTRB(l, t, r, b)
        };
        Rect.prototype = {
            getType: function() {
                return this.type
            },
            isEmpty: function() {
                return (this.width === 0 && this.height === 0)
            },
            right: function() {
                return Math.max(this.x, this.x + this.width)
            },
            left: function() {
                return Math.min(this.x, this.x + this.width)
            },
            bottom: function() {
                return Math.max(this.y, this.y + this.height)
            },
            top: function() {
                return Math.min(this.y, this.y + this.height)
            },
            center: function() {
                return new mdraw.Point(this.left() + this.width / 2, this.top() + this.height / 2)
            },
            topLeft: function() {
                return new mdraw.Point(this.left(), this.top())
            },
            topRight: function() {
                return new mdraw.Point(this.right(), this.top())
            },
            topMiddle: function() {
                return new mdraw.Point(this.x + this.width / 2, this.top())
            },
            bottomLeft: function() {
                return new mdraw.Point(this.left(), this.bottom())
            },
            bottomRight: function() {
                return new mdraw.Point(this.right(), this.bottom())
            },
            intersectsWith: function(rect) {
                return !(this.intersect(rect) === Rect.empty)
            },
            intersectsInc: function(rect) {
                if (this.bottom() <= rect.top()) {
                    return false
                }
                if (this.top() >= rect.bottom()) {
                    return false
                }
                if (this.left() >= rect.right()) {
                    return false
                }
                if (this.right() <= rect.left()) {
                    return false
                }
                return true
            },
            contains: function(rect) {
                if (rect) {
                    if (rect instanceof MindFusion.Drawing.Rect) {
                        if (this.containsPoint(rect.bottomLeft()) && this.containsPoint(rect.bottomRight()) && this.containsPoint(rect.topLeft()) && this.containsPoint(rect.topRight())) {
                            return true
                        }
                    } else {
                        if (rect instanceof MindFusion.Drawing.Point) {
                            if (this.containsPoint(rect)) {
                                return true
                            }
                        }
                    }
                }
                return false
            },
            containsPoint: function(point) {
                return this.left() <= point.x && this.right() >= point.x && this.top() <= point.y && this.bottom() >= point.y
            },
            union: function(rect) {
                if (!rect) {
                    return this
                }
                var left = Math.min(this.left(), rect.left());
                var right = Math.max(this.right(), rect.right());
                var top = Math.min(this.top(), rect.top());
                var bottom = Math.max(this.bottom(), rect.bottom());
                return new Rect(left, top, right - left, bottom - top)
            },
            intersect: function(rect) {
                if (this.bottom() < rect.top()) {
                    return Rect.empty
                }
                if (this.top() > rect.bottom()) {
                    return Rect.empty
                }
                if (this.left() > rect.right()) {
                    return Rect.empty
                }
                if (this.right() < rect.left()) {
                    return Rect.empty
                }
                var left = Math.max(this.left(), rect.left());
                var right = Math.min(this.right(), rect.right());
                var bottom = Math.min(this.bottom(), rect.bottom());
                var top = Math.max(this.top(), rect.top());
                return new Rect(left, top, right - left, bottom - top)
            },
            clone: function() {
                return new Rect(this.x, this.y, this.width, this.height)
            },
            draw: function(context, drawShadow) {
                context.save();
                context.transform.apply(context, this.transform.matrix());
                context.beginPath();
                context.rect(this.x, this.y, this.width, this.height);
                context.save();
                if (this.shadow && drawShadow != false) {
                    this.shadow.apply(context)
                }
                if (this.brush) {
                    context.fillStyle = MindFusion.Diagramming.Utils.getBrush(context, this.brush, this.getBounds());
                    context.fill()
                }
                context.restore();
                if (this.pen) {
                    context.strokeStyle = this.pen;
                    context.lineWidth = (this.strokeThickness ? this.strokeThickness : 1) / context._mf_scale;
                    mdraw.DashStyle.apply(context, this.strokeDashStyle);
                    context.stroke()
                }
                context.restore()
            },
            drawShadow: function(context) {
                if (this.shadow) {
                    context.save();
                    this.shadow.apply(context);
                    context.transform.apply(context, this.transform.matrix());
                    context.beginPath();
                    context.rect(this.x, this.y, this.width, this.height);
                    context.fillStyle = this.shadow.color;
                    context.fill();
                    context.restore()
                }
            },
            drawPath: function(context) {
                context.rect(this.x, this.y, this.width, this.height)
            },
            getBounds: function(rect) {
                return new MindFusion.Drawing.Rect(this.x, this.y, this.width, this.height)
            },
            setBounds: function(rect) {
                this.x = rect.x;
                this.y = rect.y;
                this.width = rect.width;
                this.height = rect.height
            },
            setLocation: function(point) {
                this.x = point.x;
                this.y = point.y
            },
            setCenter: function(point) {
                this.x = point.x - this.width / 2;
                this.y = point.y - this.height / 2
            },
            inflate: function(halfIncrement) {
                if (!halfIncrement) {
                    return this
                }
                var newRect = Rect.fromLTRB(this.x - halfIncrement, this.y - halfIncrement, this.right() + halfIncrement, this.bottom() + halfIncrement);
                return newRect
            },
            offset: function(x, y) {
                this.x += x;
                this.y += y
            },
            getCornerPoints: function() {
                return [this.topLeft(), this.topRight(), this.bottomRight(), this.bottomLeft()]
            },
            getSizeRect: function() {
                return new Rect(0, 0, this.width, this.height)
            },
            equals: function(rect) {
                if (!rect) {
                    return false
                }
                return (this.x === rect.x && this.y === rect.y && this.width === rect.width && this.height === rect.height)
            },
            sameSize: function(rect) {
                return this.width == rect.width && this.height == rect.height
            },
            toString: function() {
                return this.x + ", " + this.y + ", " + this.width + ", " + this.height
            },
            getSize: function() {
                return new mdraw.Size(this.width, this.height)
            },
            createSvgElement: function(svgdoc) {
                var rect = svgdoc.createElementNS("http://www.w3.org/2000/svg", "rect");
                rect.setAttribute("x", this.x);
                rect.setAttribute("y", this.y);
                rect.setAttribute("width", this.width);
                rect.setAttribute("height", this.height);
                rect.setAttribute("rx", 0);
                rect.setAttribute("ry", 0);
                var rectBrush = this.brush;
                var rectPen = this.pen;
                if (rectBrush) {
                    rect.setAttribute("fill", MindFusion.Diagramming.Utils.getBrush(null, rectBrush, this.getBounds()))
                } else {
                    rect.setAttribute("fill", "none")
                }
                if (rectPen) {
                    rect.setAttribute("stroke", rectPen)
                }
                rect.setAttribute("stroke-width", this.strokeThickness ? this.strokeThickness / 4 : 1 / 4);
                if (this.transform) {
                    rect.setAttribute("transform", this.transform.svgMatrix())
                }
                return rect
            },
            transform: null,
            pen: "black",
            strokeThickness: 0,
            brush: "transparent"
        };
        MindFusion.registerClass(Rect, "MindFusion.Drawing.Rect");
        Rect.empty = new Rect(0, 0, 0, 0)
    })(MindFusion.Drawing);
    (function(mdraw) {
        var Shadow = mdraw.Shadow = function(color, offsetX, offsetY) {
            this.color = color;
            this.offsetX = offsetX;
            this.offsetY = offsetY
        };
        Shadow.prototype = {
            apply: function(context) {
                context.shadowOffsetX = this.offsetX;
                context.shadowOffsetY = this.offsetY;
                context.shadowBlur = 4;
                context.shadowColor = this.color
            },
            createSvgElement: function(svgdoc) {
                return null
            }
        };
        MindFusion.registerClass(Shadow, "MindFusion.Drawing.Shadow")
    })(MindFusion.Drawing);
    (function(mdraw) {
        var ArrayList = MindFusion.Collections.ArrayList;
        var LineBreak = {};
        var Text = mdraw.Text = function(text, bounds) {
            this.text = text;
            if (!bounds) {
                bounds = new mdraw.Rect(0, 0, null, null)
            }
            this.bounds = bounds;
            this.x = bounds.x;
            this.y = bounds.y;
            this.width = bounds.width;
            this.height = bounds.height;
            this.clipPath = new mdraw.Path();
            this.textAlignment = Alignment.Near;
            this.lineAlignment = Alignment.Near;
            this.baseline = "middle";
            this.padding = new mdraw.Thickness(1, 1, 1, 1);
            this.type = this.constructor.__typeName
        };
        Text.prototype = {
            getType: function() {
                return this.type
            },
            clone: function() {
                var copy = new Text(this.text, this.bounds);
                copy.rotationAngle = this.rotationAngle;
                copy.clipPath = this.clipPath.clone();
                copy.textAlignment = this.textAlignment;
                copy.lineAlignment = this.lineAlignment;
                copy.padding = this.padding;
                copy.fitInBounds = this.fitInBounds;
                if (this.stroke) {
                    copy.stroke = this.stroke
                }
                if (this.strokeThickness) {
                    copy.strokeThickness = this.strokeThickness
                }
                return copy
            },
            getLines: function(context, rect) {
                this.lines = Text.wrapText(context, this.text, rect.width);
                return this.lines
            },
            draw: function(context, drawShadow) {
                if (this.text === "") {
                    return
                }
                var scale = this.ignoreTransform ? 1 : context._mf_scale;
                if (context._mf_minVisibleFontSize != undefined && this.font.size * scale < context._mf_minVisibleFontSize) {
                    return
                }
                this.scale = scale;
                context.save();
                if (!this.clipPath.empty()) {
                    this.clipPath.addToContext(context);
                    context.clip()
                }
                context.textBaseline = this.baseline;
                context.fillStyle = this.pen;
                if (this.stroke) {
                    if (this.strokeThickness !== undefined) {
                        context.lineWidth = this.strokeThickness * scale
                    }
                    context.strokeStyle = MindFusion.Diagramming.Utils.getBrush(context, this.stroke, this.bounds, true)
                }
                this.lineHeight = this.font.size * scale;
                var layoutRect = this.bounds.clone();
                this.padding.applyTo(layoutRect);
                if (!this.ignoreTransform) {
                    if (context._mf_transform) {
                        layoutRect = context._mf_transform.transformRect(layoutRect)
                    }
                    context.setTransform(1, 0, 0, 1, 0, 0);
                    context.lineWidth = context.lineWidth * scale
                }
                if (this.rotationAngle) {
                    context.transform.apply(context, this.rotationTransform(layoutRect).matrix())
                }
                if (this.enableStyledText) {
                    var sequences = this.parseStyledText(this.text);
                    this.drawStyledText(context, sequences, layoutRect, this.textAlignment, this.lineAlignment)
                } else {
                    context.font = this.font.toString(scale);
                    if (this.fitInBounds) {
                        this.getLines(context, layoutRect);
                        this.drawLines(context, layoutRect)
                    } else {
                        if (this.textAlignment == Alignment.Near) {
                            context.textAlign = "left"
                        } else {
                            if (this.textAlignment == Alignment.Center) {
                                context.textAlign = "center"
                            } else {
                                if (this.textAlignment == Alignment.Far) {
                                    context.textAlign = "right"
                                }
                            }
                        }
                        if (this.stroke) {
                            if (this.strokeThickness !== undefined) {
                                context.lineWidth = this.strokeThickness * scale
                            }
                            context.strokeText(this.text, layoutRect.x, layoutRect.y)
                        }
                        context.fillText(this.text, layoutRect.x, layoutRect.y)
                    }
                }
                this.layoutRect = layoutRect;
                context.restore()
            },
            drawLines: function(context, rect) {
                if (this.lines.length === 0) {
                    return
                }
                var height = (rect.height > 0) ? rect.height : this.lineHeight;
                var maxLines = Math.floor(height / (this.lineHeight) + 0.00001);
                if (maxLines == 0 && height > this.lineHeight * 0.9) {
                    maxLines = 1
                }
                maxLines = Math.min(maxLines, this.lines.length);
                var textHeight = maxLines * this.lineHeight;
                var y = rect.y;
                switch (this.lineAlignment) {
                    case Alignment.Center:
                        y += rect.height / 2 - textHeight / 2;
                        break;
                    case Alignment.Far:
                        y += rect.height - textHeight;
                        break
                }
                var x = rect.x;
                switch (this.textAlignment) {
                    case Alignment.Near:
                        context.textAlign = "left";
                        break;
                    case Alignment.Center:
                        x += rect.width / 2;
                        context.textAlign = "center";
                        break;
                    case Alignment.Far:
                        x += rect.width;
                        context.textAlign = "right";
                        break
                }
                if (context.textBaseline == "middle") {
                    y += this.lineHeight / 2
                }
                for (var i = 0; i < maxLines; i++) {
                    var line = this.lines[i];
                    if (!line) {
                        continue
                    }
                    var lineY = y + this.lineHeight * i;
                    if (this.stroke) {
                        if (this.strokeThickness !== undefined) {
                            context.lineWidth = this.strokeThickness * context._mf_scale
                        }
                        context.strokeText(line, x, lineY)
                    }
                    context.fillText(line, x, lineY);
                    if (this.font.underline) {
                        lineY += this.lineHeight / 2;
                        var measure = context.measureText(line);
                        switch (this.textAlignment) {
                            case Alignment.Near:
                                this.drawUnderline(context, x, lineY, measure.width);
                                break;
                            case Alignment.Center:
                                this.drawUnderline(context, x - measure.width / 2, lineY, measure.width);
                                break;
                            case Alignment.Far:
                                this.drawUnderline(context, x - measure.width, lineY, measure.width);
                                break
                        }
                    }
                }
            },
            getRotatedBounds: function() {
                var a = new mdraw.Point(this.x, this.y);
                var d = new mdraw.Point(this.x + this.width, this.y + this.height);
                var p = [a, d];
                var matrix = this.rotationTransform(this.bounds);
                matrix.transformPoints(p);
                return mdraw.Rect.fromLTRB(p[0].x, p[0].y, p[1].x, p[1].y)
            },
            setBounds: function(bounds, angle) {
                this.bounds = bounds;
                this.x = bounds.x;
                this.y = bounds.y;
                this.width = bounds.width;
                this.height = bounds.height;
                this.rotationAngle = angle || 0
            },
            getBounds: function() {
                return this.bounds
            },
            getRotationAngle: function() {
                return this.rotationAngle
            },
            getFont: function() {
                return this.font
            },
            setFont: function(value) {
                if (this.font == value) {
                    return
                }
                this.font = value
            },
            getText: function() {
                return this.text
            },
            setText: function(value) {
                if (this.text == value) {
                    return
                }
                this.text = value
            },
            rotationTransform: function(rect) {
                var matrix = new mdraw.Matrix();
                if (this.rotationAngle && this.rotationAngle !== 0) {
                    matrix.rotateAt(this.rotationAngle, rect.x + rect.width / 2, rect.y + rect.height / 2)
                }
                return matrix
            },
            drawStyledText: function(context, sequences, layoutRect, halign, valign) {
                var x = layoutRect.x;
                var y = layoutRect.y;
                var width = layoutRect.width;
                var height = layoutRect.height;
                var lines = this.getStyledLines(context, sequences, width);
                var lastFormat = null;
                var lineSize = this.lineHeight;
                var startY = lineSize;
                if (context.textBaseline == "middle") {
                    startY /= 2
                }
                if (valign == Alignment.Center) {
                    startY += height / 2 - lineSize * lines.length / 2
                }
                if (valign == Alignment.Far) {
                    startY += height - lineSize * lines.length
                }
                startY += y;
                context.textAlign = "left";
                var clip = this.clipToBounds && lines.length > 1;
                if (clip) {
                    context.save();
                    context.beginPath();
                    context.rect(layoutRect.x, layoutRect.y, layoutRect.width, layoutRect.height);
                    context.clip()
                }
                for (var i = 0; i < lines.length; i++) {
                    var line = lines[i];
                    var lx = 0;
                    if (halign == Alignment.Center) {
                        lx = width / 2 - line.width / 2
                    }
                    if (halign == Alignment.Far) {
                        lx = width - line.width
                    }
                    for (var j = 0; j < line.length; j++) {
                        var part = line[j];
                        if (part.format != lastFormat) {
                            this.applyFormat(context, part.format);
                            lastFormat = part.format
                        }
                        var scriptOffset = part.format.scriptOffset;
                        var yOffset = scriptOffset === 0 ? 0 : this.font.size * context._mf_scale * (scriptOffset > 0 ? -1 : 1) / 3;
                        for (var h = 1; h < Math.abs(scriptOffset); h++) {
                            yOffset += yOffset / 3
                        }
                        var lineY = startY + part.dy + yOffset;
                        if (!clip && (lineY < y + lineSize / 4 || lineY > y + height - lineSize / 4)) {
                            continue
                        }
                        if (this.stroke) {
                            if (this.strokeThickness !== undefined) {
                                context.lineWidth = this.strokeThickness * context._mf_scale
                            }
                            context.strokeText(part.text, x + lx + part.dx, lineY)
                        }
                        context.fillText(part.text, x + lx + part.dx, lineY);
                        if (part.format.underline) {
                            this.drawUnderline(context, x + lx + part.dx, lineY + lineSize / 2, part.advance)
                        }
                    }
                }
                if (clip) {
                    context.restore()
                }
            },
            parseStyledText: function(text) {
                if (this.cachedText == text && this.cachedSequences) {
                    return this.cachedSequences
                }
                this.cachedText = text;
                text = text.replace(/\r\n/g, "<br />").replace(/[\r\n]/g, "<br />");
                text = text.replace(/<color=/g, "<color value=");
                if (!Text.parser) {
                    Text.parser = document.createElement("div")
                }
                var parser = Text.parser;
                parser.innerHTML = text;
                var sequences = [];
                this.collectSequences(parser, sequences, {});
                this.cachedSequences = sequences;
                return sequences
            },
            collectSequences: function(node, sequences, currentFormat) {
                var nodeName = node.nodeName.toLowerCase();
                if (nodeName == "#text") {
                    var value = node.nodeValue;
                    var sequence = this.createSequence(value, currentFormat);
                    sequences.push(sequence)
                } else {
                    if (nodeName == "br") {
                        sequences.push(LineBreak)
                    } else {
                        if (nodeName == "color") {
                            nodeName += "=" + node.getAttribute("value")
                        }
                        this.addToFormat(nodeName, currentFormat);
                        for (var i = 0; i < node.childNodes.length; i++) {
                            var childNode = node.childNodes[i];
                            this.collectSequences(childNode, sequences, currentFormat)
                        }
                        this.removeFromFormat(nodeName, currentFormat)
                    }
                }
            },
            createSequence: function(text, format) {
                return {
                    text: text,
                    italic: format.i > 0,
                    bold: format.b > 0,
                    underline: format.u > 0,
                    scriptOffset: format.sup ? format.sup : 0 - (format.sub ? format.sub : 0),
                    color: format.colors ? format.colors[format.colors.length - 1] : null
                }
            },
            addToFormat: function(specifier, format) {
                if (specifier.indexOf("color") == 0) {
                    if (!format.colors) {
                        format.colors = []
                    }
                    var color = specifier.split("=")[1];
                    format.colors.push(color);
                    return
                }
                var value = format[specifier];
                if (!value) {
                    value = 0
                }
                value++;
                format[specifier] = value
            },
            removeFromFormat: function(specifier, format) {
                if (specifier.indexOf("color") == 0) {
                    format.colors.pop();
                    return
                }
                format[specifier]--
            },
            drawUnderline: function(context, x, y, length) {
                if (!this.stroke) {
                    if (this.strokeThickness !== undefined) {
                        context.lineWidth = this.strokeThickness * context._mf_scale
                    }
                    context.strokeStyle = context.fillStyle
                }
                if (context.setLineDash) {
                    context.setLineDash([])
                }
                context.beginPath();
                context.moveTo(x, y);
                context.lineTo(x + length, y);
                context.stroke()
            },
            getStyledLines: function(context, sequences, width) {
                var lines = [];
                var line = [];
                line.width = 0;
                var lineStart = true;
                var cx = 0;
                var cy = 0;
                var lineHeight = this.lineHeight;

                function newLine() {
                    lines.push(line);
                    line = [];
                    line.width = 0;
                    cy += lineHeight;
                    cx = 0;
                    lineStart = true
                }
                for (var i = 0; i < sequences.length; i++) {
                    var sequence = sequences[i];
                    if (sequence === LineBreak) {
                        newLine();
                        continue
                    }
                    this.applyFormat(context, sequence);
                    var remaining = sequence.text;
                    while (remaining.length > 0) {
                        var fittingPart = this.fitInLine(context, remaining, cx, cy, width, lineStart);
                        fittingPart.format = sequence;
                        line.push(fittingPart);
                        line.width += fittingPart.advance;
                        remaining = fittingPart.remaining;
                        if (remaining.length > 0) {
                            newLine()
                        } else {
                            cx += fittingPart.advance;
                            lineStart = false
                        }
                    }
                }
                if (line.length > 0) {
                    lines.push(line)
                }
                return lines
            },
            findWhitespace: function(text, start) {
                var index = text.substring(start).search(/\s+/);
                return index > -1 ? index + start : text.length
            },
            fitInLine: function(context, text, x, y, maxX, lineStart) {
                if (lineStart) {
                    while (text.length > 0 && /\s/.test(text.charAt(0))) {
                        text = text.substring(1)
                    }
                }
                var measure = context.measureText(text);
                if (x + measure.width > maxX) {
                    var prevWspos = 0;
                    var prevWidth = 0;
                    for (var i = 0; i <= text.length;) {
                        var wspos = this.findWhitespace(text, i);
                        measure = context.measureText(text.substring(0, wspos));
                        if (x + measure.width > maxX) {
                            var fits = text.substring(0, prevWspos);
                            if (fits.length == 0 && lineStart) {
                                return this.fitInLineWrapByChar(context, text, x, y, maxX, lineStart)
                            }
                            return {
                                remaining: text.substring(fits.length),
                                advance: prevWidth,
                                text: fits,
                                dx: x,
                                dy: y
                            }
                        }
                        prevWspos = wspos;
                        prevWidth = measure.width;
                        i = wspos + 1
                    }
                }
                return {
                    remaining: "",
                    advance: measure.width,
                    text: text,
                    dx: x,
                    dy: y
                }
            },
            fitInLineWrapByChar: function(context, text, x, y, maxX, lineStart) {
                if (lineStart) {
                    while (text.length > 0 && /\s/.test(text.charAt(0))) {
                        text = text.substring(1)
                    }
                }
                var measure = context.measureText(text);
                if (x + measure.width > maxX) {
                    var prevWidth = 0;
                    for (var i = 1; i <= text.length; i++) {
                        measure = context.measureText(text.substring(0, i));
                        if (x + measure.width > maxX) {
                            var fits = text.substring(0, i - 1);
                            if (fits.length == 0 && lineStart) {
                                fits = text.substring(0, 1);
                                prevWidth = measure.width
                            }
                            return {
                                remaining: text.substring(fits.length),
                                advance: prevWidth,
                                text: fits,
                                dx: x,
                                dy: y
                            }
                        }
                        prevWidth = measure.width
                    }
                }
                return {
                    remaining: "",
                    advance: measure.width,
                    text: text,
                    dx: x,
                    dy: y
                }
            },
            applyFormat: function(context, sequence) {
                var font = "";
                var scale = context._mf_scale;
                for (var i = 0; i < Math.abs(sequence.scriptOffset); i++) {
                    scale *= 66 / 100
                }
                if (sequence.bold) {
                    font = "bold " + font
                }
                if (sequence.italic) {
                    font = "italic " + font
                }
                font += this.font.toString(scale);
                context.font = font;
                if (sequence.color) {
                    context.fillStyle = sequence.color
                } else {
                    context.fillStyle = this.pen
                }
            },
            measureStyledText: function(context, maxWidth) {
                var m = this.padding;
                if (this.text === "") {
                    return new mdraw.Size(m.left + m.right, m.top + m.bottom)
                }
                var scale = this.ignoreTransform ? 1 : context._mf_scale;
                context.save();
                this.lineHeight = this.font.size * scale;
                if (maxWidth != Number.MAX_VALUE) {
                    maxWidth *= scale
                }
                if (!this.ignoreTransform) {
                    context.setTransform(1, 0, 0, 1, 0, 0)
                }
                var sequences = this.parseStyledText(this.text);
                var lines = this.getStyledLines(context, sequences, maxWidth);
                context.restore();
                var height = this.lineHeight * lines.length;
                var width = 0;
                for (var i = 0; i < lines.length; i++) {
                    width = Math.max(width, lines[i].width)
                }
                if (!this.ignoreTransform) {
                    width += 1
                }
                return new mdraw.Size(width / scale + m.left + m.right, height / scale + m.top + m.bottom)
            },
            getDefaultProperty: function() {
                return this.text
            },
            setDefaultProperty: function(value) {
                this.text = value
            },
            measure: function(maxWidth, maxHeight) {
                var ctx = mdraw.Component.context;
                if (!this.text) {
                    return new mdraw.Size(this.padding.width(), this.padding.height())
                }
                if (maxWidth) {
                    maxWidth -= this.padding.width()
                }
                var size = ctx.measureString(this.text, this.font, null, this.enableStyledText);
                if (maxWidth && size.width > maxWidth) {
                    var layoutWidth = maxWidth ? maxWidth : Number.MAX_VALUE;
                    var layoutRect = new mdraw.Rect(0, 0, layoutWidth, Number.MAX_VALUE);
                    size = ctx.measureString(this.text, this.font, layoutRect, this.enableStyledText)
                }
                this.desiredWidth = size.width + this.padding.width();
                this.desiredHeight = size.height + this.padding.height()
            },
            effectiveMeasuredWidth: function() {
                return this.desiredWidth
            },
            effectiveMeasuredHeight: function() {
                return this.desiredHeight
            },
            createSvgElement: function(svgdoc) {
                if (this.text == "") {
                    return null
                }
                var context = svgdoc._mf_context;
                var fillText = context.fillText;
                var scale = context._mf_scale;
                var adjustX = 8;
                var adjustY = 4;
                if (this.ignoreTransform) {
                    adjustX = 0;
                    adjustY = -1;
                    scale = 1
                }
                var g = svgdoc.createElementNS("http://www.w3.org/2000/svg", "g");
                var center = this.bounds.center();
                var transform = "";
                if (this.rotationAngle) {
                    transform += "rotate(" + this.rotationAngle + " " + center.x + " " + center.y + ")"
                }
                transform += "scale(" + (1 / scale) + ")";
                g.setAttribute("transform", transform);
                try {
                    context.fillText = function(text, x, y) {
                        var textElement = svgdoc.createElementNS("http://www.w3.org/2000/svg", "text");
                        textElement.textContent = text;
                        textElement.setAttribute("stroke", "none");
                        textElement.setAttribute("fill", context.fillStyle);
                        textElement.setAttribute("style", "font: " + context.font);
                        textElement.setAttribute("x", x - adjustX);
                        textElement.setAttribute("y", y - adjustY);
                        switch (context.textAlign) {
                            case "left":
                                textElement.setAttribute("text-anchor", "start");
                                break;
                            case "center":
                                textElement.setAttribute("text-anchor", "middle");
                                break;
                            case "right":
                                textElement.setAttribute("text-anchor", "end");
                                break
                        }
                        g.appendChild(textElement)
                    };
                    this.draw(context, false)
                } catch (e) {}
                context.fillText = fillText;
                return g
            },
            pen: "black",
            bounds: null,
            lines: null,
            font: new mdraw.Font("sans-serif", 4)
        };
        Text.wrapText = function(context, text, maxWidth) {
            var lines = text.split("\n");
            if (maxWidth == Number.MAX_VALUE) {
                return lines
            }
            var wrappedLines = [];
            ArrayList.forEach(lines, function(line) {
                Text.wrapLine(context, line, maxWidth, wrappedLines)
            });
            return wrappedLines
        };
        Text.wrapLine = function(context, text, maxWidth, lines) {
            var words = text.split(" ");
            var lastWord = "";
            for (var i = 0; i < words.length; i++) {
                var word = words[i];
                var m = context.measureText(lastWord + word).width;
                if (m < maxWidth) {
                    if (i == 0) {
                        lastWord += (word)
                    } else {
                        lastWord += (" " + word)
                    }
                } else {
                    if (lastWord != "") {
                        lines.push(lastWord)
                    }
                    lastWord = word
                }
                if (i == words.length - 1) {
                    lines.push(lastWord);
                    break
                }
            }
        };
        Text.getMinWidth = function(text, font, context, scale) {
            context.save();
            context.scale(scale, scale);
            context.font = font;
            var words = text.split(/\s{1,}/);
            var w = 0;
            var width = 0;
            for (var i = 0; i < words.length; i++) {
                w = context.measureText(words[i]).width;
                if (w > width) {
                    width = w
                }
            }
            context.restore();
            return width
        };
        MindFusion.registerClass(Text, "MindFusion.Drawing.Text");
        var Alignment = {
            Near: 0,
            Center: 1,
            Far: 2
        }
    })(MindFusion.Drawing);
    MindFusion.Drawing.Vector = function(x, y) {
        this.x = x;
        this.y = y;
        this.type = this.constructor.__typeName
    };
    MindFusion.Drawing.Vector.prototype = {
        getType: function() {
            return this.type
        },
        clone: function() {
            return new MindFusion.Drawing.Vector(this.x, this.y)
        },
        length: function() {
            return Math.sqrt(MindFusion.Drawing.Vector.dot(this, this))
        },
        lengthSquared: function() {
            return MindFusion.Drawing.Vector.dot(this, this)
        },
        negate: function() {
            return new MindFusion.Drawing.Vector(-this.x, -this.y)
        },
        normalize: function() {
            return new MindFusion.Drawing.Vector(this.x / this.length(), this.y / this.length())
        },
        toPoint: function() {
            return new MindFusion.Drawing.Point(this.x, this.y)
        }
    };
    MindFusion.Drawing.Vector.fromPoints = function(point1, point2) {
        return new MindFusion.Drawing.Vector(point2.x - point1.x, point2.y - point1.y)
    };
    MindFusion.Drawing.Vector.dot = function(vector1, vector2) {
        return (vector1.x * vector2.x) + (vector1.y * vector2.y)
    };
    MindFusion.Drawing.Vector.multiplyScalar = function(vector, value) {
        return new MindFusion.Drawing.Vector(vector.x * value, vector.y * value)
    };
    MindFusion.Drawing.Vector.divideScalar = function(vector, value) {
        return new MindFusion.Drawing.Vector(vector.x * (1 / value), vector.y * (1 / value))
    };
    MindFusion.Drawing.Vector.add = function(vector1, vector2) {
        return new MindFusion.Drawing.Vector(vector1.x + vector2.x, vector1.y + vector2.y)
    };
    MindFusion.Drawing.Vector.sub = function(vector1, vector2) {
        return new MindFusion.Drawing.Vector(vector1.x - vector2.x, vector1.y - vector2.y)
    };
    MindFusion.registerClass(MindFusion.Drawing.Vector, "MindFusion.Drawing.Vector");
    MindFusion.registerNamespace("MindFusion.Diagramming");
    (function(mdiag) {
        var Rect = MindFusion.Drawing.Rect;
        var Point = MindFusion.Drawing.Point;
        var Utils = mdiag.Utils = function() {};
        Utils.getRectPtPercent = function(point, rect) {
            var p1 = new Point(50, 50);
            if (rect.width > 0 && rect.height > 0) {
                p1.x = (point.x - rect.x) * 100 / rect.width;
                p1.y = (point.y - rect.y) * 100 / rect.height
            }
            return p1
        };
        Utils.unionRects = function(rect1, rect2) {
            if (rect1.width === 0 || rect1.height === 0) {
                return rect2
            }
            return rect1.union(rect2)
        };
        Utils.normalizeRect = function(rect) {
            var temp = new Rect(0, 0, 0, 0);
            temp.x = Math.min(rect.x, rect.right());
            temp.width = Math.abs(rect.width);
            temp.y = Math.min(rect.y, rect.bottom());
            temp.height = Math.abs(rect.height);
            return temp
        };
        Utils.inflate = function(rect, x, y) {
            if (rect.width + 2 * x < 0) {
                x = -rect.width / 2
            }
            if (rect.height + 2 * y < 0) {
                y = -rect.height / 2
            }
            return new Rect(rect.x - x, rect.y - y, rect.width + 2 * x, rect.height + 2 * y)
        };
        Utils.distToPolyline = function(point, ppoints, npoints, refSegment) {
            var minDistSquared = Number.MAX_VALUE;
            if (refSegment) {
                refSegment.value = 0
            }
            for (var s = 0; s < npoints - 1; ++s) {
                var p1 = ppoints[s];
                var p2 = ppoints[s + 1];
                var d = new MindFusion.Drawing.DistanceToSegment(point, p1, p2);
                var distSquared = d.distanceToSegmentSquared();
                if (distSquared < minDistSquared) {
                    minDistSquared = distSquared;
                    if (refSegment) {
                        refSegment.value = s
                    }
                }
            }
            return Math.sqrt(minDistSquared)
        };
        Utils.intersect = function(p1, p2, p3, p4) {
            return (this.ccw(p1, p2, p3) * this.ccw(p1, p2, p4) <= 0) && (this.ccw(p3, p4, p1) * this.ccw(p3, p4, p2) <= 0)
        };
        Utils.ccw = function(p0, p1, p2) {
            var dx1, dx2;
            var dy1, dy2;
            dx1 = p1.x - p0.x;
            dx2 = p2.x - p0.x;
            dy1 = p1.y - p0.y;
            dy2 = p2.y - p0.y;
            return ((dx1 * dy2 > dy1 * dx2) ? 1 : -1)
        };
        Utils.getIntersectionPoint = function(m1, m2, n1, n2) {
            if (m1.x == m2.x && n1.x == n2.x) {
                return undefined
            }
            if (m1.x == m2.x) {
                var a = (n1.y - n2.y) / (n1.x - n2.x);
                var b = (n1.x * n2.y - n2.x * n1.y) / (n1.x - n2.x);
                return new Point(m1.x, a * m1.x + b)
            }
            if (n1.x == n2.x) {
                if (m1.y == m2.y) {
                    return new Point(n1.x, m1.y)
                }
                var a = (m1.y - m2.y) / (m1.x - m2.x);
                var b = (m1.x * m2.y - m2.x * m1.y) / (m1.x - m2.x);
                return new Point(n1.x, a * n1.x + b)
            }
            var a1 = (m1.y - m2.y) / (m1.x - m2.x);
            var b1 = (m1.x * m2.y - m2.x * m1.y) / (m1.x - m2.x);
            var a2 = (n1.y - n2.y) / (n1.x - n2.x);
            var b2 = (n1.x * n2.y - n2.x * n1.y) / (n1.x - n2.x);
            if (a1 == a2) {
                return undefined
            }
            var res = new Point((b2 - b1) / (a1 - a2), a1 * (b2 - b1) / (a1 - a2) + b1);
            if (m1.y == m2.y) {
                res.y = m1.y
            }
            return res
        };
        Utils.getSegmentIntersection = function(s1, s2, l1, l2) {
            var pt = Utils.getIntersectionPoint(s1, s2, l1, l2);
            if (!pt) {
                return pt
            }
            var p1 = (pt.x - s1.x) * (pt.x - s2.x);
            var p2 = (pt.y - s1.y) * (pt.y - s2.y);
            if (p1 > 0.0001 || p2 > 0.0001) {
                return undefined
            }
            var pl1 = (pt.x - l1.x) * (pt.x - l2.x);
            var pl2 = (pt.y - l1.y) * (pt.y - l2.y);
            if (pl1 > 0.0001 || pl2 > 0.0001) {
                return undefined
            }
            return pt
        };
        Utils.pointInPolygon = function(point, polygon) {
            var crossings = 0;
            polygon = polygon.slice(0);
            for (var i = 0; i < polygon.length; ++i) {
                polygon[i].x -= point.x;
                polygon[i].y -= point.y
            }
            for (var i = 0; i < polygon.length; ++i) {
                var j = (i + 1) % polygon.length;
                if (polygon[i].y > 0 && polygon[j].y <= 0 || polygon[j].y > 0 && polygon[i].y <= 0) {
                    var x = (polygon[i].x * polygon[j].y - polygon[j].x * polygon[i].y) / (polygon[j].y - polygon[i].y);
                    if (x > 0) {
                        crossings++
                    }
                }
            }
            return crossings % 2 == 1
        };
        Utils.getPolygonIntersection = function(points, org, end, result) {
            var currentDistanceSq, currentIntersection, nearestDistanceSq = Number.MAX_VALUE;
            for (var i = 0; i < points.length; i++) {
                currentIntersection = Utils.getSegmentIntersection(points[i], points[(i + 1) % points.length], org, end);
                if (currentIntersection) {
                    currentDistanceSq = Utils.DistanceSq(currentIntersection, end);
                    if (currentDistanceSq < nearestDistanceSq) {
                        nearestDistanceSq = currentDistanceSq;
                        result.x = currentIntersection.x;
                        result.y = currentIntersection.y
                    }
                }
            }
            return nearestDistanceSq < Number.MAX_VALUE
        };
        Utils.getClosestSegmentPoint = function(p, a, b) {
            if (a.equals(b)) {
                return a
            }
            var dx = b.x - a.x;
            var dy = b.y - a.y;
            var dotProduct = (p.x - a.x) * dx + (p.y - a.y) * dy;
            if (dotProduct < 0) {
                return a
            }
            dotProduct = (b.x - p.x) * dx + (b.y - p.y) * dy;
            if (dotProduct < 0) {
                return b
            }
            var lf = Utils.getLeftVector({
                x: a.x - b.x,
                y: a.y - b.y
            });
            var n = new Point(p.x + lf.x, p.y + lf.y);
            return Utils.getIntersectionPoint(a, b, p, n)
        };
        Utils.getLeftVector = function(vector) {
            return {
                x: vector.y,
                y: -vector.x
            }
        };
        Utils.symmetricPoint = function(point, pivot) {
            var p = new Point(pivot.x - point.x, pivot.y - point.y);
            var pp = new Point(p.x + pivot.x, p.y + pivot.y);
            return pp
        };
        Utils.checkIntersect = function(point, rect, rad) {
            var leftX = rect.x - point.x;
            var rightX = rect.right() - point.x;
            var topY = rect.y - point.y;
            var bottomY = rect.bottom() - point.y;
            if (rightX < 0) {
                if (topY > 0) {
                    return (rightX * rightX + topY * topY < rad * rad)
                } else {
                    if (bottomY < 0) {
                        return (rightX * rightX + bottomY * bottomY < rad * rad)
                    } else {
                        return (Math.abs(rightX) < rad)
                    }
                }
            } else {
                if (leftX > 0) {
                    if (topY > 0) {
                        return (leftX * leftX + topY * topY < rad * rad)
                    } else {
                        if (bottomY < 0) {
                            return (leftX * leftX + bottomY * bottomY < rad * rad)
                        } else {
                            return (Math.abs(leftX) < rad)
                        }
                    }
                } else {
                    if (topY > 0) {
                        return (Math.abs(topY) < rad)
                    } else {
                        if (bottomY < 0) {
                            return (Math.abs(bottomY) < rad)
                        } else {
                            return true
                        }
                    }
                }
            }
        };
        Utils.minDistToRect = function(point, rect) {
            var nearest = Utils.distToRectPoint(point, rect);
            return Point.distance(point, nearest)
        };
        Utils.distToRectPoint = function(point, rect) {
            return new Point(Utils.distToRectSelect(point.x, rect.x, rect.right()), Utils.distToRectSelect(point.y, rect.y, rect.bottom()))
        };
        Utils.distToRectSelect = function(pointX, rectX1, rectX2) {
            var closer, farther;
            var r = Utils.closer(pointX, rectX1, rectX2, closer, farther);
            closer = r.a;
            farther = r.b;
            if (Utils.betweenOrEqual(pointX, closer, farther)) {
                return pointX
            } else {
                return closer
            }
        };
        Utils.equalEpsilon = function(f1, f2) {
            return Math.abs(f1 - f2) < 0.00001
        };
        Utils.pointEqualEpsilon = function(p1, p2) {
            return Utils.equalEpsilon(p1.x, p2.x) && Utils.equalEpsilon(p1.y, p2.y)
        };
        Utils.mid = function(point1, point2) {
            return new Point((point1.x + point2.x) / 2, (point1.y + point2.y) / 2)
        };
        Utils.closer = function(pivot, choice1, choice2, closer, farther) {
            var r = Utils.sort(choice1, choice2);
            choice1 = r.a;
            choice2 = r.b;
            var choice1Closer;
            if (pivot < choice1) {
                choice1Closer = true
            } else {
                if (pivot > choice2) {
                    choice1Closer = false
                } else {
                    choice1Closer = pivot - choice1 < choice2 - pivot
                }
            }
            if (choice1Closer) {
                closer = choice1;
                farther = choice2
            } else {
                closer = choice2;
                farther = choice1
            }
            return {
                a: closer,
                b: farther
            }
        };
        Utils.swap = function(a, b) {
            var tmp = a;
            a = b;
            b = tmp;
            return {
                a: a,
                b: b
            }
        };
        Utils.sort = function(a, b) {
            if (b < a) {
                var r = Utils.swap(a, b);
                a = r.a;
                b = r.b
            }
            return {
                a: a,
                b: b
            }
        };
        Utils.betweenOrEqual = function(n, boundary1, boundary2) {
            var r = Utils.sort(boundary1, boundary2);
            boundary1 = r.a;
            boundary2 = r.b;
            return Utils.betweenOrEqualSorted(n, boundary1, boundary2)
        };
        Utils.betweenOrEqualSorted = function(n, boundary1, boundary2) {
            return boundary1 <= n && n <= boundary2
        };
        Utils.subtract = function(p1, p2) {
            return {
                x: p1.x - p2.x,
                y: p1.y - p2.y
            }
        };
        Utils.offsetPointCollection = function(points, originalPoints, offset) {
            if (points.length !== originalPoints.length) {
                return
            }
            for (var i = 0; i < points.length; ++i) {
                var p = originalPoints[i].clone();
                points[i] = p.addVector(offset)
            }
        };
        Utils.rectPtFromPercent = function(point, rect) {
            return new Point(rect.x + point.x / 100 * rect.width, rect.y + point.y / 100 * rect.height)
        };
        Utils.setRect = function(rect, rect1) {
            rect.width = rect1.width;
            rect.height = rect1.height;
            rect.setLocation(rect1.topLeft())
        };
        Utils.betweenOrEqualSorted = function(n, boundary1, boundary2) {
            return boundary1 <= n && n <= boundary2
        };
        Utils.getLineHitTest = function(unit) {
            return 5 * MindFusion.Drawing.GraphicsUnit.getMillimeter(unit)
        };
        Utils.DistanceSq = function(point1, point2) {
            return (point1.x - point2.x) * (point1.x - point2.x) + (point1.y - point2.y) * (point1.y - point2.y)
        };
        Utils.radians = function(degrees) {
            return degrees / 180 * Math.PI
        };
        Utils.degrees = function(radians) {
            return radians / Math.PI * 180
        };
        Utils.rotatePointAt = function(point, pivot, angle) {
            var matrix = new MindFusion.Drawing.Matrix();
            matrix.rotateAt(angle, pivot.x, pivot.y);
            point = point.clone();
            matrix.transformPoint(point);
            return point
        };
        Utils.rotatePointsAt = function(points, pivot, angle) {
            var matrix = new MindFusion.Drawing.Matrix();
            matrix.rotateAt(angle, pivot.x, pivot.y);
            for (var i = 0, l = points.length; i < l; ++i) {
                matrix.transformPoint(points[i])
            }
        };
        Utils.getCenter = function(rect) {
            return new Point(rect.x + rect.width / 2, rect.y + rect.height / 2)
        };
        Utils.rotateRect = function(rect, pivot, angle) {
            if (angle == 0) {
                return rect
            }
            var matrix = new MindFusion.Drawing.Matrix();
            matrix.rotateAt(angle, pivot.x, pivot.y);
            return matrix.transformRect(rect)
        };
        Utils.getBounds = function(element) {
            var scroll = Utils.getPageScroll();
            var bounds = mflayer.getBounds(element);
            if (navigator.userAgent.toLowerCase().indexOf("chrome") > -1) {
                if (scroll.scrollLeft != 0 || scroll.scrollTop != 0) {
                    var rect = element.getBoundingClientRect();
                    if ((bounds.x - rect.left) < 1 && (bounds.y - rect.top) < 1) {
                        bounds.x += scroll.scrollLeft;
                        bounds.y += scroll.scrollTop
                    }
                }
            }
            return bounds
        };
        Utils.getPageScroll = function() {
            var scrollLeft = 0;
            var scrollTop = 0;
            if (window.pageXOffset != undefined) {
                scrollLeft = window.pageXOffset
            } else {
                if (document.body.scrollTop !== 0) {
                    scrollLeft = document.body.scrollLeft
                } else {
                    scrollLeft = document.documentElement.scrollLeft
                }
            }
            if (window.pageYOffset != undefined) {
                scrollTop = window.pageYOffset
            } else {
                if (document.body.scrollTop !== 0) {
                    scrollTop = document.body.scrollTop
                } else {
                    scrollTop = document.documentElement.scrollTop
                }
            }
            return {
                scrollLeft: scrollLeft,
                scrollTop: scrollTop
            }
        };
        Utils.getCursorPos = function(e, element) {
            var scroll = Utils.getPageScroll();
            var bounds = mflayer.getBounds(element);
            var x = e.clientX - bounds.x + scroll.scrollLeft;
            var y = e.clientY - bounds.y + scroll.scrollTop;
            if (navigator.userAgent.toLowerCase().indexOf("chrome") > -1) {
                if (scroll.scrollLeft != 0 || scroll.scrollTop != 0) {
                    var rect = element.getBoundingClientRect();
                    if ((bounds.x - rect.left) < 1 && (bounds.y - rect.top) < 1) {
                        x -= scroll.scrollLeft;
                        y -= scroll.scrollTop
                    }
                }
            }
            return new Point(x, y)
        };
        Utils.getClientPos = function(e) {
            var scroll = Utils.getPageScroll();
            var x = e.clientX + scroll.scrollLeft;
            var y = e.clientY + scroll.scrollTop;
            return new Point(x, y)
        };
        Utils.getChildrenByTagName = function(tagName, element) {
            var result = [];
            if (element == undefined) {
                element = document
            }
            if (element.tagName.toUpperCase() == tagName.toUpperCase()) {
                result.push(element)
            }
            var children = element.childNodes;
            for (var i = 0; i < children.length; i++) {
                if (children[i].tagName) {
                    if (children[i].tagName.toUpperCase() == tagName.toUpperCase()) {
                        result.push(children[i])
                    }
                }
            }
            return result
        };
        Utils.getBrush = function(context, brush, bounds, isPen) {
            if (!brush) {
                return (isPen) ? "#000000" : "#FFFFFF"
            }
            if (brush.type == "SolidBrush") {
                if (typeof brush.color == "string") {
                    return brush.color
                } else {
                    if (brush.color.value) {
                        return brush.color.value
                    }
                }
            } else {
                if (brush.type == "LinearGradientBrush") {
                    if (!context) {
                        return "#FFFFFF"
                    }
                    var x1 = brush.x1 ? brush.x1 : bounds.x;
                    var y1 = brush.y1 ? brush.y1 : bounds.y + bounds.height / 2;
                    var x2 = brush.x2 ? brush.x2 : bounds.x + bounds.width;
                    var y2 = brush.y2 ? brush.y2 : bounds.y + bounds.height / 2;
                    if (brush.angle) {
                        if (brush.angle === 180) {
                            x1 = bounds.x + bounds.width / 2;
                            y1 = bounds.y + bounds.height / 2;
                            x2 = bounds.x;
                            y2 = bounds.y + bounds.height / 2
                        } else {
                            if (brush.angle === 90) {
                                x1 = bounds.x + bounds.width / 2;
                                y1 = bounds.y;
                                x2 = bounds.x + bounds.width / 2;
                                y2 = bounds.y + bounds.height
                            } else {
                                if (brush.angle === 270) {
                                    x1 = bounds.x + bounds.width / 2;
                                    y1 = bounds.y + bounds.height;
                                    x2 = bounds.x + bounds.width / 2;
                                    y2 = bounds.y
                                } else {
                                    var center = new Point(bounds.x + bounds.width / 2, bounds.y + bounds.height / 2);
                                    var angle = brush.angle;
                                    var diff;
                                    angle = ((angle % 360) + 360) % 360;
                                    if (angle >= 0 && angle < 90) {
                                        var a1 = MindFusion.Geometry.cartesianToPolarDegrees(center, bounds.topRight()).a;
                                        diff = angle - a1
                                    } else {
                                        if (angle >= 90 && angle < 180) {
                                            var a1 = MindFusion.Geometry.cartesianToPolarDegrees(center, bounds.topLeft()).a;
                                            diff = angle - a1
                                        } else {
                                            if (angle >= 180 && angle < 270) {
                                                var a1 = MindFusion.Geometry.cartesianToPolarDegrees(center, bounds.bottomLeft()).a;
                                                diff = angle - a1
                                            } else {
                                                var a1 = MindFusion.Geometry.cartesianToPolarDegrees(center, bounds.bottomRight()).a;
                                                diff = angle - a1
                                            }
                                        }
                                    }
                                    var r = Math.sqrt(bounds.width * bounds.width / 4 + bounds.height * bounds.height / 4);
                                    if (diff !== 0) {
                                        r = r * Math.sin(MindFusion.Geometry.degreeToRadian(90 - Math.abs(diff)))
                                    }
                                    var start = MindFusion.Geometry.polarToCartesianDegrees(center, {
                                        a: angle,
                                        r: r
                                    });
                                    var end = MindFusion.Geometry.polarToCartesianDegrees(center, {
                                        a: angle - 180,
                                        r: r
                                    });
                                    x1 = start.x;
                                    y1 = start.y;
                                    x2 = end.x;
                                    y2 = end.y
                                }
                            }
                        }
                    }
                    var b = context.createLinearGradient(x1, y1, x2, y2);
                    if (brush.colorStops) {
                        for (var i = 0, l = brush.colorStops.length; i < l; i++) {
                            b.addColorStop(brush.colorStops[i].position, brush.colorStops[i].color)
                        }
                    } else {
                        b.addColorStop(0, brush.color1);
                        b.addColorStop(1, brush.color2)
                    }
                    return b
                } else {
                    if (brush.type == "RadialGradientBrush") {
                        if (!context) {
                            return "#FFFFFF"
                        }
                        var x1 = (brush.x1 != undefined) ? brush.x1 : bounds.center().x;
                        var y1 = (brush.y1 != undefined) ? brush.y1 : bounds.center().y;
                        var x2 = (brush.x2 != undefined) ? brush.x2 : bounds.center().x;
                        var y2 = (brush.y2 != undefined) ? brush.y2 : bounds.center().y;
                        var radius1 = (brush.radius1 != undefined) ? brush.radius1 : 0;
                        var radius2 = (brush.radius2 != undefined) ? brush.radius2 : Math.max(bounds.width, bounds.height) / 2;
                        var b = context.createRadialGradient(x1, y1, radius1, x2, y2, radius2);
                        if (brush.colorStops) {
                            for (var i = 0, l = brush.colorStops.length; i < l; i++) {
                                b.addColorStop(brush.colorStops[i].position, brush.colorStops[i].color)
                            }
                        } else {
                            b.addColorStop(0, brush.color1);
                            b.addColorStop(1, brush.color2)
                        }
                        return b
                    } else {
                        return brush
                    }
                }
            }
        };
        Utils.getBezierPt = function(points, segment, t) {
            var x0 = points[segment * 3 + 0].x;
            var y0 = points[segment * 3 + 0].y;
            var x1 = points[segment * 3 + 1].x;
            var y1 = points[segment * 3 + 1].y;
            var x2 = points[segment * 3 + 2].x;
            var y2 = points[segment * 3 + 2].y;
            var x3 = points[segment * 3 + 3].x;
            var y3 = points[segment * 3 + 3].y;
            var tt = t;
            var q0 = (1 - tt) * (1 - tt) * (1 - tt);
            var q1 = 3 * tt * (1 - tt) * (1 - tt);
            var q2 = 3 * tt * tt * (1 - tt);
            var q3 = tt * tt * tt;
            var xt = q0 * x0 + q1 * x1 + q2 * x2 + q3 * x3;
            var yt = q0 * y0 + q1 * y1 + q2 * y2 + q3 * y3;
            return new Point(xt, yt)
        };
        Utils.approximateBezier = function(points, quality, start) {
            if (start == undefined) {
                start = 0
            }
            var approximation = [];
            for (var i = start; i < points.length - 3; i += 3) {
                var p0 = points[i];
                var p1 = points[i + 1];
                var p2 = points[i + 2];
                var p3 = points[i + 3];
                Utils.addCubicBezierPoints(approximation, quality, p0.x, p0.y, p1.x, p1.y, p2.x, p2.y, p3.x, p3.y)
            }
            return approximation
        };
        Utils.addCubicBezierPoints = function(points, quality, p1x, p1y, c1x, c1y, c2x, c2y, p2x, p2y) {
            var d = 1 / quality;
            for (var t = 0; t <= 1; t += d) {
                var q1 = Math.pow(1 - t, 3);
                var q2 = 3 * Math.pow(1 - t, 2) * t;
                var q3 = 3 * (1 - t) * t * t;
                var q4 = t * t * t;
                var x = q1 * p1x + q2 * c1x + q3 * c2x + q4 * p2x;
                var y = q1 * p1y + q2 * c1y + q3 * c2y + q4 * p2y;
                points.push(new Point(x, y))
            }
        };
        Utils.addQuadraticBezierPoints = function(points, quality, p1x, p1y, cx, cy, p2x, p2y) {
            var d = 1 / quality;
            for (var t = d; t <= 1; t += d) {
                var q1 = (1 - t) * (1 - t);
                var q2 = 2 * (1 - t) * t;
                var q3 = t * t;
                var x = q1 * p1x + q2 * cx + q3 * p2x;
                var y = q1 * p1y + q2 * cy + q3 * p2y;
                points.push(new Point(x, y))
            }
        };
        Utils.addArcPoints = function(points, quality, cx, cy, radius, startAngle, endAngle, anticlockwise) {
            if (!anticlockwise) {
                while (endAngle < startAngle) {
                    endAngle += 2 * Math.PI
                }
            } else {
                while (endAngle > startAngle) {
                    endAngle -= 2 * Math.PI
                }
            }
            var d = (endAngle - startAngle) / quality;
            var a = startAngle;
            for (var i = 0; i <= quality; i++) {
                var x = cx + radius * Math.cos(a);
                var y = cy + radius * Math.sin(a);
                points.push(new Point(x, y));
                a += d
            }
        };
        Utils.getApproximatingContext = function() {
            return {
                points: [],
                beginPath: function() {},
                moveTo: function(x, y) {
                    this.points.push(new Point(x, y))
                },
                lineTo: function(x, y) {
                    this.ensureStart();
                    this.points.push(new Point(x, y))
                },
                bezierCurveTo: function(cp1x, cp1y, cp2x, cp2y, x, y) {
                    this.ensureStart();
                    var p1 = this.lastPoint();
                    Utils.addCubicBezierPoints(this.points, 20, p1.x, p1.y, cp1x, cp1y, cp2x, cp2y, x, y)
                },
                quadraticCurveTo: function(cpx, cpy, x, y) {
                    this.ensureStart();
                    var p1 = this.lastPoint();
                    Utils.addQuadraticBezierPoints(this.points, 20, p1.x, p1.y, cpx, cpy, x, y)
                },
                arc: function(x, y, radius, startAngle, endAngle, anticlockwise) {
                    Utils.addArcPoints(this.points, 20, x, y, radius, startAngle, endAngle, anticlockwise)
                },
                ensureStart: function() {
                    if (this.points.length == 0) {
                        this.points.push(new Point(0, 0))
                    }
                },
                lastPoint: function() {
                    return this.points[this.points.length - 1]
                },
                transform: {
                    apply: function(context, matrix) {
                        this.matrix = MindFusion.Drawing.Matrix.fromValues(matrix)
                    }
                },
                transformAndGetPoints: function() {
                    if (this.transform.matrix) {
                        this.transform.matrix.transformPoints(this.points)
                    }
                    return this.points
                }
            }
        };
        Utils.arcToBezierCurves = function(x, y, w, h, a, sw) {
            var points = [];
            var endAngle, startAngle, e;
            var clockwise = sw > 0;
            e = a + sw;
            a = this.radians(a);
            e = this.radians(e);
            startAngle = a;
            for (var i = 0; i < 4; i++) {
                if (clockwise) {
                    if (startAngle >= e) {
                        break
                    }
                    endAngle = Math.min(startAngle + Math.PI / 2, e)
                } else {
                    if (startAngle <= e) {
                        break
                    }
                    endAngle = Math.max(startAngle - Math.PI / 2, e)
                }
                var bezier = this.arcSegmentToBezier(x, y, w, h, startAngle, endAngle);
                for (var j = 0; j < bezier.length; j++) {
                    points.push(bezier[j])
                }
                startAngle += Math.PI / 2 * (clockwise ? 1 : -1)
            }
            return points
        };
        Utils.arcSegmentToBezier = function(x, y, w, h, s, e) {
            var rx = w / 2,
                ry = h / 2;
            var cx = x + rx,
                cy = y + ry;
            var sCos = Math.cos(s),
                sSin = Math.sin(s);
            var eCos = Math.cos(e),
                eSin = Math.sin(e);
            var coef = 4 / 3 * (1 - Math.cos((e - s) / 2)) / Math.sin((e - s) / 2);
            var points = [new Point(sCos, sSin), new Point(sCos - coef * sSin, sSin + coef * sCos), new Point(eCos + coef * eSin, eSin - coef * eCos), new Point(eCos, eSin)];
            for (var i = 0; i < points.length; i++) {
                points[i].x *= rx;
                points[i].x += cx;
                points[i].y *= ry;
                points[i].y += cy
            }
            return points
        };
        Utils.newRect = function(center, size) {
            var half = size / 2;
            return new Rect(center.x - half, center.y - half, size, size)
        };
        Utils.stringFormat = function() {
            var formatted = (typeof(String) == "function") ? arguments[0] : this;
            for (var i = 0; i < arguments.length; i++) {
                var regexp = new RegExp("\\{" + i + "\\}", "gi");
                formatted = formatted.replace(regexp, arguments[i + 1])
            }
            return formatted
        };
        Utils.escapeNewLine = function(string) {
            if (string != null && string != "") {
                return string.replace(/\n/g, "\\n")
            } else {
                return string
            }
        };
        Utils.offset1 = function(rect, x, y) {
            return new Rect(rect.x + x, rect.y + y, rect.width, rect.height)
        };
        Utils.offset = function(rect, point) {
            return Utils.offset1(rect, point.x, point.y)
        };
        Utils.isNumber = function(num) {
            return !isNaN(num - 0)
        };
        Utils.isFloat = function(number) {
            return !/^-?\d+$/.test(String(number))
        };
        Utils.sign = function(x) {
            if (+x === x) {
                return (x === 0) ? x : (x > 0) ? 1 : -1
            }
            return NaN
        };
        Utils.getFitTextStep = function(currUnit) {
            return MindFusion.Drawing.GraphicsUnit.convert(0.4, currUnit, MindFusion.Drawing.GraphicsUnit.Millimeter)
        };
        Utils.formatString = function() {
            var formatted = arguments[0];
            for (var i = 1; i < arguments.length; i++) {
                var regexp = new RegExp("\\{" + (i - 1) + "\\}", "gi");
                formatted = formatted.replace(regexp, arguments[i])
            }
            return formatted
        };
        Utils.colorStringToHex = function(string) {
            var rgb = Utils.parseColor(string);
            if (rgb) {
                var alpha = "FF";
                if (typeof rgb.alpha !== "undefined") {
                    alpha = ("00" + parseInt(rgb.alpha * 256).toString(16)).slice(-2)
                }
                return "#" + alpha + ("00" + rgb.red.toString(16)).slice(-2) + ("00" + rgb.green.toString(16)).slice(-2) + ("00" + rgb.blue.toString(16)).slice(-2)
            }
            return "#FFFFFFFF"
        };
        Utils.parseColor = function(value) {
            var colorRegEx = "^#{0,1}?[A-Fa-f0-9]{3,6}$";
            var colorString = value;
            var knownColor = Utils.checkKnownColor(colorString);
            if (knownColor) {
                colorString = knownColor
            }
            if (colorString.match(colorRegEx)) {
                var rgb = Utils.hexToRgb(colorString);
                if (rgb) {
                    return rgb
                }
            } else {
                var div = document.createElement("div");
                div.style.backgroundColor = colorString;
                var color = div.style.backgroundColor;
                var rgb = Utils.stringToRgb(color);
                if (rgb) {
                    return rgb
                }
            }
            throw new Error("Unknown color code: " + value)
        };
        Utils.hexToRgb = function(value) {
            if (!value || value.length < 3 || value.length > 7) {
                return
            }
            value = value.replace("#", "");
            var r, g, b;
            var length = 2;
            if (value.length == 3) {
                value = value[0] + value[0] + value[1] + value[1] + value[2] + value[2]
            }
            var v = value.substring(value.length - length);
            b = parseInt(v, 16);
            value = value.substring(0, value.length - length);
            length = value.length == 1 ? 1 : 2;
            v = value.substring(value.length - length);
            value = value.substring(0, value.length - length);
            g = parseInt(v, 16);
            if (value.length == 0) {
                r = 0
            } else {
                r = parseInt(value, 16)
            }
            return {
                red: r,
                green: g,
                blue: b
            }
        };
        Utils.stringToRgb = function(string) {
            if (string.length == 0) {
                return null
            }
            if (string == "transparent") {
                return {
                    red: 255,
                    green: 255,
                    blue: 255,
                    alpha: 0
                }
            }
            if (string.match(/[0-9,\s]+/g) == null) {
                return null
            }
            var arr = string.match(/(rgba?)|(\d+(\.\d+)?%?)|(\.\d+)/g);
            if (arr.length != 4 && arr.length != 5) {
                return null
            }
            var r = +arr[1];
            var g = +arr[2];
            var b = +arr[3];
            var a = (arr.length == 5) ? +arr[4] : 1;
            if ((r >= 0 && r <= 255) && (g >= 0 && g <= 255) && (b >= 0 && b <= 255) && (a >= 0 && a <= 1)) {
                return {
                    red: r,
                    green: g,
                    blue: b,
                    alpha: a
                }
            }
            return null
        };
        Utils.rgbToString = function(r, g, b, a) {
            if (r != undefined && g != undefined && b != undefined) {
                if (a == undefined) {
                    a = 1
                }
                return Utils.formatString("rgba({0},{1},{2},{3})", r, g, b, a)
            }
            return "rgba(0,0,0,1)"
        };
        Utils.checkKnownColor = function(value) {
            var colors = Utils.knownColors;
            var v = value.toLowerCase();
            for (var key in colors) {
                if (v == key) {
                    return colors[key]
                }
            }
            return null
        };
        Utils.knownColors = {
            aliceblue: "#f0f8ff",
            antiquewhite: "#faebd7",
            aqua: "#00ffff",
            aquamarine: "#7fffd4",
            azure: "#f0ffff",
            beige: "#f5f5dc",
            bisque: "#ffe4c4",
            black: "#000000",
            blanchedalmond: "#ffebcd",
            blue: "#0000ff",
            blueviolet: "#8a2be2",
            brown: "#a52a2a",
            burlywood: "#deb887",
            cadetblue: "#5f9ea0",
            chartreuse: "#7fff00",
            chocolate: "#d2691e",
            coral: "#ff7f50",
            cornflowerblue: "#6495ed",
            cornsilk: "#fff8dc",
            crimson: "#dc143c",
            cyan: "#00ffff",
            darkblue: "#00008b",
            darkcyan: "#008b8b",
            darkgoldenrod: "#b8860b",
            darkgray: "#a9a9a9",
            darkgreen: "#006400",
            darkkhaki: "#bdb76b",
            darkmagenta: "#8b008b",
            darkolivegreen: "#556b2f",
            darkorange: "#ff8c00",
            darkorchid: "#9932cc",
            darkred: "#8b0000",
            darksalmon: "#e9967a",
            darkseagreen: "#8fbc8f",
            darkslateblue: "#483d8b",
            darkslategray: "#2f4f4f",
            darkslategrey: "#2f4f4f",
            darkturquoise: "#00ced1",
            darkviolet: "#9400d3",
            deeppink: "#ff1493",
            deepskyblue: "#00bfff",
            dimgray: "#696969",
            dodgerblue: "#1e90ff",
            feldspar: "#d19275",
            firebrick: "#b22222",
            floralwhite: "#fffaf0",
            forestgreen: "#228b22",
            fuchsia: "#ff00ff",
            gainsboro: "#dcdcdc",
            ghostwhite: "#f8f8ff",
            gold: "#ffd700",
            goldenrod: "#daa520",
            gray: "#808080",
            grey: "#808080",
            green: "#008000",
            greenyellow: "#adff2f",
            honeydew: "#f0fff0",
            hotpink: "#ff69b4",
            indianred: "#cd5c5c",
            indigo: "#4b0082",
            ivory: "#fffff0",
            khaki: "#f0e68c",
            lavender: "#e6e6fa",
            lavenderblush: "#fff0f5",
            lawngreen: "#7cfc00",
            lemonchiffon: "#fffacd",
            lightblue: "#add8e6",
            lightcoral: "#f08080",
            lightcyan: "#e0ffff",
            lightgoldenrodyellow: "#fafad2",
            lightgray: "#d3d3d3",
            lightgrey: "#d3d3d3",
            lightgreen: "#90ee90",
            lightpink: "#ffb6c1",
            lightsalmon: "#ffa07a",
            lightseagreen: "#20b2aa",
            lightskyblue: "#87cefa",
            lightslateblue: "#8470ff",
            lightslategray: "#778899",
            lightslategrey: "#778899",
            lightsteelblue: "#b0c4de",
            lightyellow: "#ffffe0",
            lime: "#00ff00",
            limegreen: "#32cd32",
            linen: "#faf0e6",
            magenta: "#ff00ff",
            maroon: "#800000",
            mediumaquamarine: "#66cdaa",
            mediumblue: "#0000cd",
            mediumorchid: "#ba55d3",
            mediumpurple: "#9370d8",
            mediumseagreen: "#3cb371",
            mediumslateblue: "#7b68ee",
            mediumspringgreen: "#00fa9a",
            mediumturquoise: "#48d1cc",
            mediumvioletred: "#c71585",
            midnightblue: "#191970",
            mintcream: "#f5fffa",
            mistyrose: "#ffe4e1",
            moccasin: "#ffe4b5",
            navajowhite: "#ffdead",
            navy: "#000080",
            oldlace: "#fdf5e6",
            olive: "#808000",
            olivedrab: "#6b8e23",
            orange: "#ffa500",
            orangered: "#ff4500",
            orchid: "#da70d6",
            palegoldenrod: "#eee8aa",
            palegreen: "#98fb98",
            paleturquoise: "#afeeee",
            palevioletred: "#d87093",
            papayawhip: "#ffefd5",
            peachpuff: "#ffdab9",
            peru: "#cd853f",
            pink: "#ffc0cb",
            plum: "#dda0dd",
            powderblue: "#b0e0e6",
            purple: "#800080",
            red: "#ff0000",
            rosybrown: "#bc8f8f",
            royalblue: "#4169e1",
            saddlebrown: "#8b4513",
            salmon: "#fa8072",
            sandybrown: "#f4a460",
            seagreen: "#2e8b57",
            seashell: "#fff5ee",
            sienna: "#a0522d",
            silver: "#c0c0c0",
            skyblue: "#87ceeb",
            slateblue: "#6a5acd",
            slategray: "#708090",
            snow: "#fffafa",
            springgreen: "#00ff7f",
            steelblue: "#4682b4",
            tan: "#d2b48c",
            teal: "#008080",
            thistle: "#d8bfd8",
            tomato: "#ff6347",
            turquoise: "#40e0d0",
            violet: "#ee82ee",
            violetred: "#d02090",
            wheat: "#f5deb3",
            white: "#ffffff",
            whitesmoke: "#f5f5f5",
            yellow: "#ffff00",
            yellowgreen: "#9acd32"
        };
        Utils.Base64 = {
            _keyStr: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
            encode: function(input) {
                var output = "";
                var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
                var i = 0;
                input = Utils.Base64._utf8_encode(input);
                while (i < input.length) {
                    chr1 = input.charCodeAt(i++);
                    chr2 = input.charCodeAt(i++);
                    chr3 = input.charCodeAt(i++);
                    enc1 = chr1 >> 2;
                    enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
                    enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
                    enc4 = chr3 & 63;
                    if (isNaN(chr2)) {
                        enc3 = enc4 = 64
                    } else {
                        if (isNaN(chr3)) {
                            enc4 = 64
                        }
                    }
                    output = output + Utils.Base64._keyStr.charAt(enc1) + Utils.Base64._keyStr.charAt(enc2) + Utils.Base64._keyStr.charAt(enc3) + Utils.Base64._keyStr.charAt(enc4)
                }
                return output
            },
            decode: function(input) {
                var output = "";
                var chr1, chr2, chr3;
                var enc1, enc2, enc3, enc4;
                var i = 0;
                input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
                while (i < input.length) {
                    enc1 = Utils.Base64._keyStr.indexOf(input.charAt(i++));
                    enc2 = Utils.Base64._keyStr.indexOf(input.charAt(i++));
                    enc3 = Utils.Base64._keyStr.indexOf(input.charAt(i++));
                    enc4 = Utils.Base64._keyStr.indexOf(input.charAt(i++));
                    chr1 = (enc1 << 2) | (enc2 >> 4);
                    chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
                    chr3 = ((enc3 & 3) << 6) | enc4;
                    output = output + String.fromCharCode(chr1);
                    if (enc3 != 64) {
                        output = output + String.fromCharCode(chr2)
                    }
                    if (enc4 != 64) {
                        output = output + String.fromCharCode(chr3)
                    }
                }
                output = Utils.Base64._utf8_decode(output);
                return output
            },
            _utf8_encode: function(string) {
                string = string.replace(/\r\n/g, "\n");
                var utftext = "";
                for (var n = 0; n < string.length; n++) {
                    var c = string.charCodeAt(n);
                    if (c < 128) {
                        utftext += String.fromCharCode(c)
                    } else {
                        if ((c > 127) && (c < 2048)) {
                            utftext += String.fromCharCode((c >> 6) | 192);
                            utftext += String.fromCharCode((c & 63) | 128)
                        } else {
                            utftext += String.fromCharCode((c >> 12) | 224);
                            utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                            utftext += String.fromCharCode((c & 63) | 128)
                        }
                    }
                }
                return utftext
            },
            _utf8_decode: function(utftext) {
                var string = "";
                var i = 0;
                var c = c1 = c2 = 0;
                while (i < utftext.length) {
                    c = utftext.charCodeAt(i);
                    if (c < 128) {
                        string += String.fromCharCode(c);
                        i++
                    } else {
                        if ((c > 191) && (c < 224)) {
                            c2 = utftext.charCodeAt(i + 1);
                            string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                            i += 2
                        } else {
                            c2 = utftext.charCodeAt(i + 1);
                            c3 = utftext.charCodeAt(i + 2);
                            string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                            i += 3
                        }
                    }
                }
                return string
            }
        };
        Utils.escapeHtml = function(string) {
            var div = document.createElement("div");
            div.appendChild(document.createTextNode(string));
            return div.innerHTML
        };
        Utils.unescapeHtml = function(escapedString) {
            var div = document.createElement("div");
            div.innerHTML = escapedString;
            var child = div.childNodes[0];
            return child ? child.nodeValue : ""
        };
        MindFusion.registerClass(Utils, "MindFusion.Diagramming.Utils")
    })(MindFusion.Diagramming);
    MindFusion.registerNamespace("MindFusion.Controls");
    MindFusion.Controls.MouseCursors = {
        Auto: "auto",
        Crosshair: "crosshair",
        Default: "default",
        Pointer: "pointer",
        Move: "move",
        HorizontalResize: "e-resize",
        CounterDiagonalResize: "ne-resize",
        DiagonalResize: "nw-resize",
        VerticalResize: "n-resize",
        Text: "text",
        Wait: "wait",
        Help: "help",
        Progress: "progress",
        Inherit: "inherit",
        Rotate: "all-scroll",
        Nothing: "null",
        NotAllowed: "not-allowed",
        Pan: "all-scroll"
    };
    MindFusion.Controls.ButtonType = {
        ScrollLeft: 0,
        ScrollUp: 1,
        ScrollRight: 2,
        ScrollDown: 3,
        ZoomIn: 4,
        ZoomOut: 5,
        ZoomScale: 6,
        ZoomSlider: 7
    };
    MindFusion.Controls.Orientation = {
        Horizontal: 0,
        Vertical: 1
    };
    MindFusion.Controls.TickPosition = {
        None: 0,
        Left: 1,
        Right: 2,
        Both: 3
    };
    MindFusion.Controls.Alignment = {
        Near: 0,
        Center: 1,
        Far: 2
    };
    (function(con) {
        var Utils = MindFusion.Diagramming.Utils;
        var Button = con.Button = function(parent, type) {
            this.parent = parent;
            this.type = type;
            this.pen = "#000000";
            this.brush = "transparent";
            this.decorationPen = "#000000";
            this.decorationBrush = "transparent";
            this.hotPen = "#000000";
            this.hotBrush = "orange";
            this.hotDecorationPen = "#000000";
            this.hotDecorationBrush = "transparent"
        };
        Button.prototype = {
            draw: function(context) {
                context.save();
                var isHot = this == this.parent.currentManipulator;
                if (this.shape) {
                    this.shape.pen = isHot ? this.hotPen : this.pen;
                    this.shape.brush = Utils.getBrush(context, isHot ? this.hotBrush : this.brush, this.shape.getBounds());
                    context.save();
                    context.shadowOffsetX = 1;
                    context.shadowOffsetY = 1;
                    context.shadowBlur = 2;
                    context.shadowColor = this.parent.shadowColor;
                    this.shape.draw(context);
                    context.closePath();
                    context.restore()
                }
                if (this.decoration) {
                    this.decoration.pen = isHot ? this.hotDecorationPen : this.decorationPen;
                    this.decoration.brush = Utils.getBrush(context, isHot ? this.hotDecorationBrush : this.decorationBrush, this.decoration.getBounds());
                    this.decoration.draw(context)
                }
                context.restore()
            },
            hitTest: function(mousePosition) {
                if (this.bounds.containsPoint(mousePosition)) {
                    return this
                }
                return null
            },
            setBounds: function(bounds) {
                if (this.bounds != bounds) {
                    this.bounds = bounds
                }
            },
            setShape: function(shape) {
                if (this.shape != shape) {
                    this.shape = shape
                }
            },
            setDecoration: function(decoration) {
                if (this.decoration != decoration) {
                    this.decoration = decoration
                }
            }
        };
        MindFusion.registerClass(Button, "MindFusion.Controls.Button")
    })(MindFusion.Controls);
    (function(con) {
        var Rect = MindFusion.Drawing.Rect;
        var Path = MindFusion.Drawing.Path;
        var Text = MindFusion.Drawing.Text;
        var Utils = MindFusion.Diagramming.Utils;
        var Alignment = MindFusion.Controls.Alignment;
        var ZoomControl = con.ZoomControl = function(element) {
            mflayer.initializeBase(ZoomControl, this, [element]);
            this.controls = {};
            this.manipulators = [];
            this.padding = 5;
            this.minZoomFactor = 0;
            this.maxZoomFactor = 200;
            this.zoomStep = 10;
            this.snapToZoomStep = true;
            this.zoomFactor = 100;
            this.scrollStep = 10;
            this.showLabel = true;
            this.cornerRadius = 4;
            this.padding = 2;
            this.tickPosition = con.TickPosition.Left;
            this.cornerRadius = 2;
            this.fill = "#FFFFFF";
            this.backColor = "#FFFFFF";
            this.activeColor = "skyBlue";
            this.borderColor = "rgba(77,83,94,1)";
            this.innerColor = "rgba(91,91,91,1)";
            this.shadowColor = "gray";
            this.textColor = "rgba(77,83,94,1)";
            this.enabled = true;
            this.init()
        };
        ZoomControl.prototype = {
            initialize: function() {
                mflayer.callBaseMethod(ZoomControl, this, "initialize");
                this.postDataField = document.getElementById(this.get_element().id + "_PostData");
                var canvas = Utils.getChildrenByTagName("canvas", this.get_element())[0];
                if (typeof canvas.getContext !== "undefined") {
                    this.canvas = canvas;
                    this.context = canvas.getContext("2d")
                }
                mflayer.addHandlers(this._element, {
                    mousedown: mflayer.createDelegate(this, this.onMouseDown)
                });
                mflayer.addHandlers(this._element, {
                    mousemove: mflayer.createDelegate(this, this.onMouseMove)
                });
                this.mouseUpDelegate = mflayer.createDelegate(this, this.onMouseUp);
                mflayer.addHandlers(document, {
                    mouseup: this.mouseUpDelegate
                })
            },
            dispose: function() {
                mflayer.removeHandler(document, "mouseup", this.mouseUpDelegate);
                mflayer.callBaseMethod(ZoomControl, this, "dispose")
            },
            registerForSubmit: function(id) {
                var field = document.getElementById(id);
                if (field) {
                    var form = field.form;
                    if (form) {
                        form.addEventListener("submit", mflayer.createDelegate(this, mflayer.createCallback(this.preparePostback, {
                            id: id
                        })))
                    }
                }
            },
            preparePostback: function(sender, args) {
                var field = document.getElementById(args.id);
                if (field) {
                    field.value = this.toJson()
                }
            },
            postback: function() {
                if (this.get_element()) {
                    window.__doPostBack(this.get_element().id, this.postDataField)
                }
            },
            init: function() {
                var bounds = mflayer.getBounds(this.get_element());
                var width = bounds.width;
                var height = bounds.height;
                this.bounds = new Rect(0, 0, width, height);
                this.orientation = width > height ? con.Orientation.Horizontal : con.Orientation.Vertical;
                this.minDim = (this.orientation == con.Orientation.Vertical) ? this.bounds.width : this.bounds.height;
                this.maxDim = (this.orientation == con.Orientation.Vertical) ? this.bounds.height : this.bounds.width;
                this.radius = (this.minDim - 2 * this.padding) / 2;
                this.center = this.minDim / 2;
                this.spacing = this.buttonSize = this.minDim / 5;
                this.decorationSize = this.minDim / 10
            },
            createControls: function() {
                var scroller = new con.Button(this, con.ButtonType.None);
                var path = new Path();
                path.arcTo(this.minDim / 2, this.minDim / 2, (this.minDim - this.padding * 2) / 2, 0, 2 * Math.PI, false);
                scroller.shape = path;
                scroller.pen = this.borderColor;
                scroller.brush = this.fill;
                var upButton = new con.Button(this, con.ButtonType.ScrollUp);
                upButton.bounds = new Rect(this.center - this.radius / 4, this.center - this.radius, this.radius / 2, this.radius);
                path = new Path();
                path.arcTo(this.minDim / 2, this.minDim / 2, (this.minDim - this.padding * 2) / 2, (5 * Math.PI) / 4, (7 * Math.PI) / 4, false);
                path.lineTo(this.center, this.minDim / 2);
                path.close();
                upButton.shape = path;
                path = new Path();
                path.moveTo(this.center - this.decorationSize, this.decorationSize * 3);
                path.lineTo(this.center, this.decorationSize * 2);
                path.lineTo(this.center + this.decorationSize, this.decorationSize * 3);
                upButton.decoration = path;
                var leftButton = new con.Button(this, con.ButtonType.ScrollLeft);
                leftButton.bounds = new Rect(this.center - this.radius, this.center - this.radius / 4, this.radius, this.radius / 2);
                path = new Path();
                path.arcTo(this.minDim / 2, this.minDim / 2, (this.minDim - this.padding * 2) / 2, (3 * Math.PI) / 4, (5 * Math.PI) / 4, false);
                path.lineTo(this.center, this.minDim / 2);
                path.close();
                leftButton.shape = path;
                path = new Path();
                path.moveTo(this.decorationSize * 3, this.center - this.decorationSize);
                path.lineTo(this.decorationSize * 2, this.center);
                path.lineTo(this.decorationSize * 3, this.center + this.decorationSize);
                leftButton.decoration = path;
                var downButton = new con.Button(this, con.ButtonType.ScrollDown);
                downButton.bounds = new Rect(this.center - this.radius / 4, this.center, this.radius / 2, this.radius);
                path = new Path();
                path.arcTo(this.minDim / 2, this.minDim / 2, (this.minDim - this.padding * 2) / 2, Math.PI / 4, (3 * Math.PI) / 4, false);
                path.lineTo(this.center, this.minDim / 2);
                path.close();
                path.quadraticCurveTo(this.center, this.minDim, this.spacing, this.minDim - this.spacing);
                downButton.shape = path;
                path = new Path();
                path.moveTo(this.center - this.decorationSize, this.minDim - this.decorationSize * 3);
                path.lineTo(this.center, this.minDim - this.decorationSize * 2);
                path.lineTo(this.center + this.decorationSize, this.minDim - this.decorationSize * 3);
                downButton.decoration = path;
                var rightButton = new con.Button(this, con.ButtonType.ScrollRight);
                rightButton.bounds = new Rect(this.center, this.center - this.radius / 4, this.radius, this.radius / 2);
                path = new Path();
                path.arcTo(this.minDim / 2, this.minDim / 2, (this.minDim - this.padding * 2) / 2, (7 * Math.PI) / 4, Math.PI / 4, false);
                path.lineTo(this.center, this.minDim / 2);
                path.close();
                rightButton.shape = path;
                path = new Path();
                path.moveTo(this.minDim - this.decorationSize * 3, this.center - this.decorationSize);
                path.lineTo(this.minDim - this.decorationSize * 2, this.center);
                path.lineTo(this.minDim - this.decorationSize * 3, this.center + this.decorationSize);
                rightButton.decoration = path;
                upButton.pen = upButton.hotPen = downButton.pen = downButton.hotPen = leftButton.pen = leftButton.hotPen = rightButton.pen = rightButton.hotPen = "transparent";
                upButton.hotBrush = {
                    type: "LinearGradientBrush",
                    color1: this.activeColor,
                    color2: "white",
                    angle: 270
                };
                rightButton.hotBrush = {
                    type: "LinearGradientBrush",
                    color1: this.activeColor,
                    color2: "white",
                    angle: 0
                };
                leftButton.hotBrush = {
                    type: "LinearGradientBrush",
                    color1: this.activeColor,
                    color2: "white",
                    angle: 180
                };
                downButton.hotBrush = {
                    type: "LinearGradientBrush",
                    color1: this.activeColor,
                    color2: "white",
                    angle: 90
                };
                upButton.decorationBrush = upButton.hotDecorationBrush = leftButton.decorationBrush = leftButton.hotDecorationBrush = downButton.decorationBrush = downButton.hotDecorationBrush = rightButton.decorationBrush = rightButton.hotDecorationBrush = "transparent";
                upButton.decorationPen = upButton.hotDecorationPen = leftButton.decorationPen = leftButton.hotDecorationPen = downButton.decorationPen = downButton.hotDecorationPen = rightButton.decorationPen = rightButton.hotDecorationPen = this.innerColor;
                var zoomInButton = new con.Button(this, con.ButtonType.ZoomIn);
                var bounds = new Rect(this.maxDim - this.minDim - this.spacing, this.center - this.spacing, this.spacing * 2, this.spacing * 2);
                if (this.orientation == con.Orientation.Vertical) {
                    bounds = new Rect(this.center - this.spacing, this.minDim + this.spacing, this.spacing * 2, this.spacing * 2)
                }
                zoomInButton.bounds = bounds;
                path = new Path();
                path.addRoundRect(bounds, this.cornerRadius);
                zoomInButton.shape = path;
                path = new Path();
                if (this.orientation == con.Orientation.Vertical) {
                    path.moveTo(this.center - this.buttonSize / 2, this.minDim + this.buttonSize * 2);
                    path.lineTo(this.center + this.buttonSize / 2, this.minDim + this.buttonSize * 2);
                    path.moveTo(this.center, this.minDim + this.buttonSize * 2 - this.buttonSize / 2);
                    path.lineTo(this.center, this.minDim + this.buttonSize * 2 + this.buttonSize / 2)
                } else {
                    path.moveTo(this.maxDim - this.minDim, this.center + this.buttonSize / 2);
                    path.lineTo(this.maxDim - this.minDim, this.center - this.buttonSize / 2);
                    path.moveTo(this.maxDim - this.minDim - this.buttonSize / 2, this.center);
                    path.lineTo(this.maxDim - this.minDim + this.buttonSize / 2, this.center)
                }
                zoomInButton.decoration = path;
                zoomInButton.pen = zoomInButton.hotPen = this.borderColor;
                zoomInButton.brush = this.fill;
                zoomInButton.decorationPen = zoomInButton.hotDecorationPen = this.innerColor;
                zoomInButton.hotBrush = {
                    type: "LinearGradientBrush",
                    color1: this.activeColor,
                    color2: "white",
                    angle: 30
                };
                var zoomOutButton = new con.Button(this, con.ButtonType.ZoomOut);
                bounds = new Rect(this.minDim + this.spacing, this.center - this.spacing, this.spacing * 2, this.spacing * 2);
                if (this.orientation == con.Orientation.Vertical) {
                    bounds = new Rect(this.center - this.spacing, this.maxDim - this.minDim - this.spacing, this.spacing * 2, this.spacing * 2)
                }
                zoomOutButton.bounds = bounds;
                path = new Path();
                path.addRoundRect(bounds, this.cornerRadius);
                zoomOutButton.shape = path;
                path = new Path();
                if (this.orientation == con.Orientation.Vertical) {
                    path.moveTo(this.center - this.buttonSize / 2, this.maxDim - this.minDim);
                    path.lineTo(this.center + this.buttonSize / 2, this.maxDim - this.minDim)
                } else {
                    path.moveTo(this.minDim + this.buttonSize * 2 - this.buttonSize / 2, this.center);
                    path.lineTo(this.minDim + this.buttonSize * 2 + this.buttonSize / 2, this.center)
                }
                zoomOutButton.decoration = path;
                zoomOutButton.pen = zoomOutButton.hotPen = this.borderColor;
                zoomOutButton.brush = this.fill;
                zoomOutButton.decorationPen = zoomOutButton.hotDecorationPen = this.innerColor;
                zoomOutButton.hotBrush = {
                    type: "LinearGradientBrush",
                    color1: this.activeColor,
                    color2: "white",
                    angle: 30
                };
                var scale = new con.Button(this, con.ButtonType.ZoomScale);
                var ticks = Math.round((this.maxZoomFactor - this.minZoomFactor) / this.zoomStep) + 2;
                var scaleLen = this.maxDim - this.minDim * 2 - this.spacing * 4;
                var tickLen = (scaleLen - this.spacing) / (ticks - 2);
                bounds = new Rect(this.minDim + this.spacing * 3, this.center - this.spacing, scaleLen, this.spacing * 2);
                if (this.orientation == con.Orientation.Vertical) {
                    bounds = new Rect(this.center - this.spacing, this.minDim + this.spacing * 3, this.spacing * 2, scaleLen)
                }
                scale.bounds = bounds;
                path = new Path();
                if (this.orientation == con.Orientation.Vertical) {
                    path.addRect(this.center - this.spacing / 4, this.minDim + this.spacing * 3, this.spacing / 2, scaleLen);
                    if (this.tickPosition == con.TickPosition.Left || this.tickPosition == con.TickPosition.Both) {
                        for (var i = 0; i < ticks - 1; i++) {
                            path.moveTo(this.center - this.spacing / 2, this.minDim + this.spacing * 3 + this.spacing / 2 + tickLen * i);
                            path.lineTo(this.center - this.spacing, this.minDim + this.spacing * 3 + this.spacing / 2 + tickLen * i)
                        }
                    }
                    if (this.tickPosition == con.TickPosition.Right || this.tickPosition == con.TickPosition.Both) {
                        for (var i = 0; i < ticks - 1; i++) {
                            path.moveTo(this.center + this.spacing / 2, this.minDim + this.spacing * 3 + this.spacing / 2 + tickLen * i);
                            path.lineTo(this.center + this.spacing, this.minDim + this.spacing * 3 + this.spacing / 2 + tickLen * i)
                        }
                    }
                } else {
                    path.addRect(this.minDim + this.spacing * 3, this.center - this.spacing / 4, scaleLen, this.spacing / 2);
                    if (this.tickPosition == con.TickPosition.Left || this.tickPosition == con.TickPosition.Both) {
                        for (var i = 0; i < ticks - 1; i++) {
                            path.moveTo(this.minDim + this.spacing * 3 + this.spacing / 2 + tickLen * i, this.center - this.spacing / 2);
                            path.lineTo(this.minDim + this.spacing * 3 + this.spacing / 2 + tickLen * i, this.center - this.spacing)
                        }
                    }
                    if (this.tickPosition == con.TickPosition.Right || this.tickPosition == con.TickPosition.Both) {
                        for (var i = 0; i < ticks - 1; i++) {
                            path.moveTo(this.minDim + this.spacing * 3 + this.spacing / 2 + tickLen * i, this.center + this.spacing / 2);
                            path.lineTo(this.minDim + this.spacing * 3 + this.spacing / 2 + tickLen * i, this.center + this.spacing)
                        }
                    }
                }
                scale.decoration = path;
                scale.pen = scale.hotPen = scale.hotBrush = "transparent";
                scale.decorationBrush = scale.hotDecorationBrush = this.fill;
                scale.decorationPen = scale.hotDecorationPen = this.innerColor;
                var slider = new con.Button(this, con.ButtonType.ZoomSlider);
                slider.pen = slider.hotPen = this.borderColor;
                slider.brush = this.fill;
                slider.hotBrush = {
                    type: "LinearGradientBrush",
                    color1: this.activeColor,
                    color2: "white",
                    angle: 30
                };
                var label;
                if (this.showLabel) {
                    var bounds = new Rect(this.maxDim - this.minDim / 2 + this.spacing / 2, this.center, this.minDim, this.minDim);
                    if (this.orientation == con.Orientation.Vertical) {
                        bounds = new Rect(this.center, this.maxDim - this.minDim / 2 + this.spacing / 2, this.minDim, this.minDim)
                    }
                    label = new Text(this.zoomFactor + "%", bounds);
                    label.font = new MindFusion.Drawing.Font("sans-serif", 10);
                    label.textAlignment = Alignment.Center;
                    label.lineAlignment = Alignment.Center;
                    label.pen = this.textColor
                }
                this.controls = {
                    scroller: scroller,
                    upButton: upButton,
                    leftButton: leftButton,
                    downButton: downButton,
                    rightButton: rightButton,
                    zoomInButton: zoomInButton,
                    zoomOutButton: zoomOutButton,
                    scale: scale,
                    slider: slider
                };
                if (this.showLabel) {
                    this.controls.label = label
                }
                this.manipulators = [upButton, leftButton, downButton, rightButton, zoomInButton, zoomOutButton, slider, scale]
            },
            repaint: function() {
                if (!this.context) {
                    return
                }
                this.canvas.width = this.canvas.width;
                this.context.rect(this.bounds.x, this.bounds.y, this.bounds.width, this.bounds.height);
                this.context.fillStyle = this.backColor;
                this.context.fill();
                for (var c in this.controls) {
                    if (this.controls[c].draw) {
                        this.controls[c].draw(this.context)
                    }
                }
            },
            fromJson: function(json) {
                if (json > "") {
                    var obj = mflayer.fromJson(json);
                    this.targetId = obj.targetId;
                    this.padding = obj.padding;
                    this.minZoomFactor = obj.minZoomFactor;
                    this.maxZoomFactor = obj.maxZoomFactor;
                    this.zoomStep = obj.zoomStep;
                    this.scrollStep = obj.scrollStep;
                    this.snapToZoomStep = obj.snapToZoomStep;
                    this.showLabel = obj.showLabel;
                    this.tickPosition = obj.tickPosition;
                    this.cornerRadius = obj.cornerRadius;
                    this.fill = obj.fill;
                    this.backColor = obj.backColor;
                    this.activeColor = obj.activeColor;
                    this.borderColor = obj.borderColor;
                    this.innerColor = obj.innerColor;
                    this.shadowColor = obj.shadowColor;
                    this.textColor = obj.textColor;
                    this.createControls();
                    this.setZoomFactorInternal(obj.zoomFactor, true, false);
                    this.setEnabled(obj.enabled);
                    this.setAutoPostBack(obj.autoPostBack);
                    var thisObj = this;
                    setTimeout(function() {
                        return thisObj.prepare()
                    }, 100)
                }
            },
            prepare: function() {
                var target = mflayer.findControl(this.targetId);
                if (target) {
                    this.target = target;
                    if (target.addEventListener) {
                        target.addEventListener("zoomChanged", mflayer.createDelegate(this, this.onZoomChanged))
                    }
                    this.repaint()
                }
            },
            toJson: function() {
                var json = {
                    id: this.get_element().id,
                    targetId: this.targetId,
                    padding: this.padding,
                    minZoomFactor: this.minZoomFactor,
                    maxZoomFactor: this.maxZoomFactor,
                    zoomFactor: this.zoomFactor,
                    zoomStep: this.zoomStep,
                    scrollStep: this.scrollStep,
                    snapToZoomStep: this.snapToZoomStep,
                    showLabel: this.showLabel,
                    tickPosition: this.tickPosition,
                    cornerRadius: this.cornerRadius,
                    fill: this.fill,
                    backColor: this.backColor,
                    activeColor: this.activeColor,
                    borderColor: this.borderColor,
                    innerColor: this.innerColor,
                    shadowColor: this.shadowColor,
                    textColor: this.textColor,
                    enabled: this.enabled,
                    autoPostBack: this.autoPostBack
                };
                return mflayer.toJson(json)
            },
            setTarget: function(target) {
                this.createControls();
                this.setZoomFactorInternal(this.zoomFactor, true, false);
                this.target = target;
                if (target) {
                    target.addEventListener("zoomChanged", mflayer.createDelegate(this, this.onZoomChanged))
                }
                this.repaint()
            },
            onZoomChanged: function() {
                this.setZoomFactorInternal(this.target.zoomFactor, true, false)
            },
            onMouseDown: function(e) {
                if (!this.enabled) {
                    return
                }
                this.mouseDownPoint = Utils.getCursorPos(e, this.get_element());
                var mnp = this.hitTestManipulators(this.mouseDownPoint);
                if (mnp) {
                    this.onButtonMouseDown(e, mnp)
                }
                this.currentManipulator = mnp;
                this.repaint()
            },
            onMouseMove: function(e) {
                if (!this.enabled) {
                    return
                }
                if (this.mouseDownPoint) {
                    if (this.currentManipulator) {
                        if (this.currentManipulator.type == con.ButtonType.ZoomSlider) {
                            this.onSliderMove(e)
                        }
                    }
                }
            },
            onMouseUp: function(e) {
                if (!this.enabled) {
                    return
                }
                var currentPoint = Utils.getCursorPos(e, this.get_element());
                clearInterval(this.timer);
                if (this.mouseDownPoint != null) {
                    if (currentPoint.distance(this.mouseDownPoint) < 2) {
                        var mnp = this.hitTestManipulators(this.mouseDownPoint);
                        if (mnp) {
                            this.onButtonClick(e, mnp)
                        }
                    }
                }
                this.mouseDownPoint = null;
                this.currentManipulator = null;
                this.repaint()
            },
            onSliderMove: function(e) {
                var currentPoint = Utils.getCursorPos(e, this.get_element());
                var scaleStart = this.minDim + this.spacing * 3;
                var scaleLen = this.maxDim - this.minDim * 2 - this.spacing * 5;
                var point = currentPoint;
                point.x -= this.spacing / 2;
                point.y -= this.spacing / 2;
                if (this.orientation == con.Orientation.Vertical) {
                    var pos = Math.min(scaleLen, Math.max(point.y - scaleStart, 0));
                    var percent = pos / scaleLen;
                    var value = Math.round((this.maxZoomFactor - this.minZoomFactor) - (this.maxZoomFactor - this.minZoomFactor) * percent)
                } else {
                    var pos = Math.min(scaleLen, Math.max(point.x - scaleStart, 0));
                    var percent = pos / scaleLen;
                    var value = Math.round((this.maxZoomFactor - this.minZoomFactor) * percent)
                }
                this.setZoomFactorInternal(this.minZoomFactor + value, true)
            },
            hitTestManipulators: function(mousePosition) {
                if (!this.manipulators) {
                    return false
                }
                for (var i = 0; i < this.manipulators.length; i++) {
                    var mnp = this.manipulators[i];
                    if (mnp.hitTest(mousePosition)) {
                        return mnp
                    }
                }
                return null
            },
            setZoomFactorInternal: function(value, applyConstraints, updateTarget) {
                if (!applyConstraints) {
                    this.zoomFactor = value
                } else {
                    if (this.snapToZoomStep && value > this.minZoomFactor && value < this.maxZoomFactor) {
                        var result = this.minZoomFactor + (Math.round((value - this.minZoomFactor) / this.zoomStep) * this.zoomStep);
                        this.zoomFactor = Math.min(this.maxZoomFactor, Math.max(this.minZoomFactor, result))
                    } else {
                        this.zoomFactor = Math.min(this.maxZoomFactor, Math.max(this.minZoomFactor, value))
                    }
                }
                this.updateControls();
                this.repaint();
                if (updateTarget != false) {
                    if (this.target != null) {
                        this.target.setZoomLevel(this.zoomFactor)
                    }
                }
            },
            updateControls: function() {
                var scaleLen = this.maxDim - this.minDim * 2 - this.spacing * 5;
                var percent = (this.zoomFactor - this.minZoomFactor) / (this.maxZoomFactor - this.minZoomFactor);
                var position = (scaleLen * percent) + this.spacing / 2;
                var bounds = new Rect(this.minDim + this.spacing * 2 + this.spacing / 2 + position, this.center - this.spacing, this.spacing, this.spacing * 2);
                if (this.orientation == con.Orientation.Vertical) {
                    position = scaleLen - (scaleLen * percent) + this.spacing / 2;
                    bounds = new Rect(this.center - this.spacing, this.minDim + this.spacing * 2 + this.spacing / 2 + position, this.spacing * 2, this.spacing)
                }
                this.controls.slider.setBounds(bounds);
                var path = new Path();
                path.addRoundRect(bounds, this.cornerRadius);
                this.controls.slider.shape = path;
                if (this.showLabel) {
                    this.controls.label.text = this.zoomFactor + "%"
                }
                this.repaint()
            },
            onButtonMouseDown: function(e, button) {
                if (!this.target) {
                    return
                }
                thisObj = this;
                switch (button.type) {
                    case con.ButtonType.ScrollLeft:
                        this.timer = setInterval(function() {
                            thisObj.target.setScroll(thisObj.target.getScrollX() - thisObj.scrollStep, thisObj.target.getScrollY())
                        }, 100);
                        break;
                    case con.ButtonType.ScrollUp:
                        this.timer = setInterval(function() {
                            thisObj.target.setScroll(thisObj.target.getScrollX(), thisObj.target.getScrollY() - thisObj.scrollStep)
                        }, 100);
                        break;
                    case con.ButtonType.ScrollRight:
                        this.timer = setInterval(function() {
                            thisObj.target.setScroll(thisObj.target.getScrollX() + thisObj.scrollStep, thisObj.target.getScrollY())
                        }, 100);
                        break;
                    case con.ButtonType.ScrollDown:
                        this.timer = setInterval(function() {
                            thisObj.target.setScroll(thisObj.target.getScrollX(), thisObj.target.getScrollY() + thisObj.scrollStep)
                        }, 100);
                        break
                }
            },
            onButtonClick: function(e, button) {
                switch (button.type) {
                    case con.ButtonType.ZoomIn:
                        this.setZoomFactorInternal(this.zoomFactor + this.zoomStep, true);
                        break;
                    case con.ButtonType.ZoomOut:
                        this.setZoomFactorInternal(this.zoomFactor - this.zoomStep, true);
                        break;
                    case con.ButtonType.ZoomScale:
                        this.onSliderMove(e);
                        break;
                    case con.ButtonType.ScrollLeft:
                        if (this.target) {
                            this.target.setScroll(this.target.getScrollX() - this.scrollStep, this.target.getScrollY())
                        }
                        break;
                    case con.ButtonType.ScrollUp:
                        if (this.target) {
                            this.target.setScroll(this.target.getScrollX(), this.target.getScrollY() - this.scrollStep)
                        }
                        break;
                    case con.ButtonType.ScrollRight:
                        if (this.target) {
                            this.target.setScroll(this.target.getScrollX() + this.scrollStep, this.target.getScrollY())
                        }
                        break;
                    case con.ButtonType.ScrollDown:
                        if (this.target) {
                            this.target.setScroll(this.target.getScrollX(), this.target.getScrollY() + this.scrollStep)
                        }
                        break
                }
                if (this.postDataField) {
                    this.preparePostback(this, this.postDataField.id);
                    if (this.autoPostBack) {
                        this.postback()
                    }
                }
            },
            setEnabled: function(value) {
                this.enabled = value
            },
            getEnabled: function() {
                return this.enabled
            },
            getAutoPostBack: function() {
                return this.autoPostBack
            },
            setAutoPostBack: function(value) {
                this.autoPostBack = value
            },
            setZoomFactor: function(value) {
                if (this.zoomFactor !== value) {
                    this.setZoomFactorInternal(value)
                }
            },
            getZoomFactor: function() {
                return this.zoomFactor
            },
            setMinZoomFactor: function(value) {
                if (this.minZoomFactor !== value) {
                    this.minZoomFactor = value;
                    this.createControls();
                    this.updateControls();
                    this.repaint()
                }
            },
            getMinZoomFactor: function() {
                return this.minZoomFactor
            },
            setMaxZoomFactor: function(value) {
                if (this.maxZoomFactor !== value) {
                    this.maxZoomFactor = value;
                    this.createControls();
                    this.updateControls();
                    this.repaint()
                }
            },
            getMaxZoomFactor: function() {
                return this.maxZoomFactor
            },
            setZoomStep: function(value) {
                if (this.zoomStep !== value) {
                    this.zoomStep = value;
                    this.createControls();
                    this.updateControls();
                    this.repaint()
                }
            },
            getZoomStep: function() {
                return this.zoomStep
            },
            setScrollStep: function(value) {
                if (this.scrollStep !== value) {
                    this.scrollStep = value;
                    this.createControls();
                    this.updateControls();
                    this.repaint()
                }
            },
            getScrollStep: function() {
                return this.scrollStep
            },
            setBackColor: function(value) {
                if (this.backColor !== value) {
                    this.backColor = value;
                    this.repaint()
                }
            },
            getBackColor: function() {
                return this.backColor
            },
            setFill: function(value) {
                if (this.fill !== value) {
                    this.fill = value;
                    this.createControls();
                    this.updateControls();
                    this.repaint()
                }
            },
            getFill: function() {
                return this.fill
            },
            setActiveColor: function(value) {
                if (this.activeColor !== value) {
                    this.activeColor = value;
                    this.createControls();
                    this.updateControls();
                    this.repaint()
                }
            },
            getActiveColor: function() {
                return this.activeColor
            },
            setBorderColor: function(value) {
                if (this.borderColor !== value) {
                    this.borderColor = value;
                    this.createControls();
                    this.updateControls();
                    this.repaint()
                }
            },
            getBorderColor: function() {
                return this.borderColor
            },
            setInnerColor: function(value) {
                if (this.innerColor !== value) {
                    this.innerColor = value;
                    this.createControls();
                    this.updateControls();
                    this.repaint()
                }
            },
            getInnerColor: function() {
                return this.innerColor
            },
            setShadowColor: function(value) {
                if (this.shadowColor !== value) {
                    this.shadowColor = value;
                    this.createControls();
                    this.updateControls();
                    this.repaint()
                }
            },
            getShadowColor: function() {
                return this.shadowColor
            },
            setTextColor: function(value) {
                if (this.textColor !== value) {
                    this.textColor = value;
                    this.createControls();
                    this.updateControls();
                    this.repaint()
                }
            },
            getTextColor: function() {
                return this.textColor
            },
            setShowLabel: function(value) {
                if (this.showLabel !== value) {
                    this.showLabel = value;
                    this.createControls();
                    this.updateControls();
                    this.repaint()
                }
            },
            getShowLabel: function() {
                return this.showLabel
            },
            setTickPosition: function(value) {
                if (this.tickPosition !== value) {
                    this.tickPosition = value;
                    this.createControls();
                    this.updateControls();
                    this.repaint()
                }
            },
            getTickPosition: function() {
                return this.tickPosition
            },
            setSnapToZoomStep: function(value) {
                if (this.snapToZoomStep !== value) {
                    this.snapToZoomStep = value;
                    this.createControls();
                    this.updateControls();
                    this.repaint()
                }
            },
            getSnapToZoomStep: function() {
                return this.snapToZoomStep
            },
            setPadding: function(value) {
                if (this.padding !== value) {
                    this.padding = value;
                    this.createControls();
                    this.updateControls();
                    this.repaint()
                }
            },
            getPadding: function() {
                return this.padding
            },
            setCornerRadius: function(value) {
                if (this.cornerRadius !== value) {
                    this.cornerRadius = value;
                    this.createControls();
                    this.updateControls();
                    this.repaint()
                }
            },
            getCornerRadius: function() {
                return this.cornerRadius
            }
        };
        MindFusion.Controls.ZoomControl.create = function(element) {
            return mflayer.createControl(MindFusion.Controls.ZoomControl, null, null, null, element)
        };
        MindFusion.Controls.ZoomControl.find = function(element, parent) {
            return mflayer.findControl(element, parent)
        };
        MindFusion.registerClass(ZoomControl, "MindFusion.Controls.ZoomControl", "Control")
    })(MindFusion.Controls);
    var ex = MindFusion;
    ex.Dictionary = MindFusion.Collections.Dictionary;
    return ex
}));