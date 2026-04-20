// SPDX-License-Identifier: MIT
pragma solidity ^0.8.0;

import "./RoleManager.sol";

contract InvestmentTracker is RoleManager {
    // Investment structure
    struct Investment {
        address investor;
        uint256 amount;
        string assetName;
        bool isApproved;
    }

    // Mapping to store investments by ID
    mapping(uint256 => Investment) public investments;

    // Event for approved investments
    event InvestmentApproved(uint256 id);

    function submitInvestment(
        uint256 _id,
        uint256 _amount,
        string memory _assetName
    ) public {
        require(
            investments[_id].investor == address(0),
            "Investment ID already exists"
        );

        investments[_id] = Investment({
            investor: msg.sender,
            amount: _amount,
            assetName: _assetName,
            isApproved: false
        });
    }

    function approveInvestment(uint256 _id) public onlyRole(SYSTEM_ADMIN_ROLE) {
        require(
            investments[_id].investor != address(0),
            "Investment does not exist"
        );

        require(!investments[_id].isApproved, "Investment already approved");

        investments[_id].isApproved = true;

        emit InvestmentApproved(_id);
    }
}
