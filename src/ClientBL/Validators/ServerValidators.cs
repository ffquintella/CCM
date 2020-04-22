using System;
using System.Text.RegularExpressions;
using ClientBL.Exceptions;

namespace ClientBL.Validators
{
    public static class ServerValidators
    {
        public static ValidationResponse ValidateServerName(string serverName, bool newServer = false){

            if (serverName == null || serverName.Length == 0){
                return ValidationResponse.FieldCanotBeEmpty;
            }

            var sm = new ServerManager();

            if(newServer == false){
                var server = sm.LoadServer(serverName);
                if (server.Name == serverName) return ValidationResponse.OK; 
            }else{
                try
                {
                    var server = sm.LoadServer(serverName);
                    if (server.Name == serverName) return ValidationResponse.AlreadyExists;
                }catch{
                    
                    return ValidationResponse.OK;
                }
                 
            }


            return ValidationResponse.UnidentifiedError;

        }

        public static ValidationResponse ValidateFQDN(string fqdn){

            if (fqdn == null || fqdn.Length == 0)
            {
                return ValidationResponse.FieldCanotBeEmpty;
            }

            //Regex regex = new Regex(@"^[a-zA-Z0-9][a-zA-Z0-9-_]{0,61}[a-zA-Z0-9]{0,1}\.([a-zA-Z]{1,6}|[a-zA-Z0-9-]{1,30}\.[a-zA-Z]{2,3})$");


            Regex regex = new Regex(@"^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])$");

            Match match = regex.Match(fqdn);
            if (match.Success)
            {
                return ValidationResponse.OK;
            }else{
                
                return ValidationResponse.InvalidFormation;
            }


        }
    }
}
