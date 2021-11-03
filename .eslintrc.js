module.exports = {
    "env": {
        "browser": true,
        "es2021": true
    },
    "extends": [
        "plugin:vue/base",
        "plugin:vue/vue3-strongly-recommended",
        "plugin:vue/vue3-recommended",
        "plugin:vue/recommended",
    ],
    "parserOptions": {
        "ecmaVersion": 13,
    },
    "plugins": [],
    "rules": {
        "vue/html-indent": ["error", 4, {
            "attribute": 1,
            "baseIndent": 1,
            "closeBracket": 0,
            "alignAttributesVertically": true,
            "ignores": []
        }],
        "vue/script-indent": ["error", 4, {
            "baseIndent": 1,
            "switchCase": 0,
            "ignores": []
        }],
        "vue/html-self-closing": ["error", {
            "html": {
                "void": "never",
                "normal": "always",
                "component": "always"
            },
            "svg": "always",
            "math": "always"
        }]
    }
};
