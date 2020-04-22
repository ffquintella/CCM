using System;
using System.Net;
using System.Threading.Tasks;
using Domain;
using Domain.Protocol;
using RestSharp;
using Services.Helpers;

namespace Services
{
    public class AccountService : BaseService
    {
        public Account GetUserAccount(User user)
        {
            var client = RestClientHelper.GetAuthenticatedClient();
            var request = new RestRequest("/Accounts/" + user.AccountId);

            var result = client.Get<Account>(request);

            if (result.StatusCode == HttpStatusCode.OK)
            {
                return result.Data;
            }

            return null;
        }

        public ObjectOperationResponse Delete(long accountid)
        {

            if (accountid < 0)
            {
                return new ObjectOperationResponse()
                {
                    IdRef = accountid,
                    Message = "Account id invalid",
                    Status = ObjectOperationStatus.Error
                };
            }
            
            var client = RestClientHelper.GetAuthenticatedClient();
            IRestResponse<ObjectOperationResponse> result;
            
            var request = new RestRequest(string.Format("/Accounts/{0}", accountid));
            result = client.Delete<ObjectOperationResponse>(request);
            
            if (result.StatusCode == HttpStatusCode.OK || result.StatusCode == HttpStatusCode.Accepted)
            {
                return result.Data;
            }
            
            return new ObjectOperationResponse()
            {
                IdRef = accountid,
                Message = "Uknown error",
                Status = ObjectOperationStatus.Error
            };
        }

        public ObjectOperationResponse Save(Account account)
        {
            var saveResult = new ObjectOperationResponse()
            {
                Status = ObjectOperationStatus.Error
            };
            
            if (account == null)
            {
                saveResult.Message = "User cannot be null";
                return saveResult;
            }
            
            var client = RestClientHelper.GetAuthenticatedClient();

            IRestResponse<ObjectOperationResponse> result;
            
            if (account.Id == -1)
            {
                var request = new RestRequest("/Accounts");
                request.AddJsonBody(account);
                result = client.Post<ObjectOperationResponse>(request);
            }
            else
            {
                var request = new RestRequest(string.Format("/Accounts/{0}", account.Id));
                request.AddJsonBody(account);
                result = client.Put<ObjectOperationResponse>(request);
            }


            if (result.StatusCode == HttpStatusCode.OK || result.StatusCode == HttpStatusCode.Created)
            {
                return result.Data;
            }
            else
            {
                switch (result.StatusCode)
                {
                    case HttpStatusCode.Forbidden:
                        saveResult.Status = ObjectOperationStatus.Forbidden;
                        return saveResult;
                    case HttpStatusCode.NotFound:
                        saveResult.Status = ObjectOperationStatus.NotFound;
                        return saveResult;
                    case HttpStatusCode.InternalServerError:
                        return result.Data;
                }
            }

            return saveResult;
        }
    }
}