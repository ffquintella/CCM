using System;
using System.Text.RegularExpressions;

namespace ClientBL.Validators
{
    public static class AppValidators
    {
        public static ValidationResponse ValidateAppName(string appName, bool newApp = false)
        {

            if (appName == null || appName.Length == 0)
            {
                return ValidationResponse.FieldCanotBeEmpty;
            }

            var am = new AppManager();

            if (newApp == false)
            {
                var app = am.LoadApp(appName);
                if (app.Name == appName) return ValidationResponse.OK;
            }
            else
            {
                try
                {
                    var app = am.LoadApp(appName);
                    if (app.Name == appName) return ValidationResponse.AlreadyExists;
                }
                catch
                {

                    return ValidationResponse.OK;
                }

            }


            return ValidationResponse.UnidentifiedError;

        }

        public static ValidationResponse ValidateKey(string key)
        {

            var conf = Configurations.Instance;

            if (key == null || key.Length <= 0)
            {
                return ValidationResponse.FieldCanotBeEmpty;
            }

            if (key.Length != conf.AppKeySize)
            {
                return ValidationResponse.FieldTooShort;
            }

            Regex regex = new Regex(@"^[a-zA-Z0-9]{" + conf.AppKeySize + "," + conf.AppKeySize + "}$");
            Match match = regex.Match(key);
            if (match.Success)
            {
                return ValidationResponse.OK;
            }
            else
            {

                return ValidationResponse.InvalidFormation;
            }


        }
    }
}
