using System.Net;
using Domain.Exceptions;
using Domain.Protocol;
using Domain.Security;
using Domain.System;
using RestSharp;
using Services.Helpers;

namespace Services.System
{
    public class ParameterService: BaseService
    {
        private ConsolidatedSystemParameters _consolidatedSystemParameters;
        
        public ConsolidatedSystemParameters GetAllSystemParameters()
        {
            if (_consolidatedSystemParameters == null)
            {
                var client = RestClientHelper.GetAuthenticatedClient();
                var request = new RestRequest("/Parameters");

                var response = client.Get<ConsolidatedSystemParameters>(request);

                if (response.StatusCode == HttpStatusCode.OK)
                {
                    _consolidatedSystemParameters = response.Data;
                }
                else
                {
                    throw  new ParameterUnavaliableException(response.ErrorMessage);
                }
            }

            return _consolidatedSystemParameters;
        }

        public PasswordComplexity GetSystemPasswordComplexity(bool refresh = false)
        {
            if(_consolidatedSystemParameters == null ) _consolidatedSystemParameters = new ConsolidatedSystemParameters();

            if (_consolidatedSystemParameters.PasswordComplexity == null || refresh)
            {
                var client = RestClientHelper.GetAuthenticatedClient();
                var request = new RestRequest("/Parameters/PasswordComplexity");
                
                var response = client.Get<PasswordComplexity>(request);
                
                if (response.StatusCode == HttpStatusCode.OK)
                {
                    _consolidatedSystemParameters.PasswordComplexity = response.Data;
                }
                else
                {
                    throw  new ParameterUnavaliableException(response.ErrorMessage);
                }
            }

            return _consolidatedSystemParameters.PasswordComplexity;
        }

        public ObjectOperationResponse SaveSystemPasswordComplexity()
        {
            var result = new ObjectOperationResponse();
            
            if (_consolidatedSystemParameters == null || _consolidatedSystemParameters.PasswordComplexity == null)
            {
                result.Status = ObjectOperationStatus.Error;
                result.Message = "Invalid System Parameters State Code:301";
                return result;
            }

            var client = RestClientHelper.GetAuthenticatedClient();
            var request = new RestRequest("/Parameters/PasswordComplexity");

            request.AddJsonBody(_consolidatedSystemParameters.PasswordComplexity);
                
            var response = client.Put<ObjectOperationResponse>(request);

            if (response.StatusCode == HttpStatusCode.OK)
            {
                return response.Data;
            }

            result.Status = ObjectOperationStatus.Error;
            result.Message = "Communications Error Code:302";
            
            return result;
        }
    }
}